
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Hbase Calculator &ndash; Pure</title>


<link rel="stylesheet"
	href="http://yui.yahooapis.com/pure/0.2.1/pure-min.css">

<link rel="stylesheet"
	href="http://purecss.io/combo/1.5.4?/css/layouts/pricing.css">


<script src="http://use.typekit.net/ajf8ggy.js"></script>
<script>
    try { Typekit.load(); } catch (e) {}

</script>



<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-41480445-1', 'purecss.io');
ga('send', 'pageview');


</script>


</head>
<body>

	<?php 

	/*
	 * Define all variables required for calculation
	* storagevalue
	* memoryvalue
	* regionsize
	* regionrs
	* javaheap
	* cacheprct
	*/

	$memstore_flush_size = 0; //MBs
	$reion_size = 0;		 //Gbs
	$block_size = 0;			 //KBs
	$dfs_replication = 0;
	$region_rs = 0;			// Maximum number of regions per servers

	// Hardware configuration : Per region server

	$disk_space = 0;	//Gb
	$cost = 0;			// Dollars
	$raw_hdfs = 0; //Raw HDFS storage
	$hwProfiles;



	//Other used variables
	$num_regions_storage = 0;
	$new_heap_size = 0;
	$cache_needs = 0;
	$in_table_storage = 0; //Input table size
	$memstore_needs = 0; // Maximum memstore required per Region server
	$memstore_prcnt = 0;
	$cache_prcnt = 0;

	$num_rs_storage = 0;
	$num_rs_performance = 0;
	$num_rs_cache = 0;

	$num_rs_rr_1 = 0;
	$num_rs_wr_1 = 0;
	$num_rs_sr_1 = 0;
	$num_rs_rr_2 = 0;
	$num_rs_wr_2 = 0;
	$num_rs_sr_2 = 0;

	$gets_per_sec = 0;
	$puts_per_sec = 0;
	$scan_per_sec = 0;
	$table_avg_record_size = 0;
	$hwreadrate_1 = 0;
	$hwreadrate_2 = 0;
	

	/*
	 * OpePerSec class to hold record size(bytes) and Operations/sec
	*/
	Class OperationPerSec{
		private $record_size = 0;
		private $operation_per_sec = 0;

		function getRecordSize(){
			return $this->record_size;
		}

		function setRecordSize($in_record_size){
			$this->record_size = $in_record_size;
		}

		function getOpsPerSec(){
			return $this->operation_per_sec;
		}

		function setOpsPerSec($in_operation_per_sec){
			$this->operation_per_sec = $in_operation_per_sec;
		}


	}

	/*
	 * HWProfile class holds details about a specific h/w
	* Memory, Storage, CPU, Network
	* Throughputs: Get/sec, Put/sec and Scan/sec
	*/

	Class HWProfile {
		private $memory_bytes = 0;
		private $storage_bytes = 0;
		private $network = 0;
		private $replication_factor = 3;
		private $cost_dollars = 0;
		private $java_heap_bytes = 0;
		private $cache = 0;

		// Record size(bytes) vs Ops/sec
		private $put_throughput ;
		private $get_throughput ;
		private $scan_throughput ;

		function getmemoryBytes(){
			return $this->memory_bytes;
		}

		function setmemoryBtes($in_memory_bytes){

			$this->memory_bytes = $in_memory_bytes;
		}

		function getstorageBytes(){
			return $this->storage_bytes;
		}

		function setstorageBytes($in_storage_bytes){

			$this->storage_bytes=$in_storage_bytes;
		}

		function getnetwork(){

			return $this->network;
		}

		function setnetwork($in_network){

			$this->network = $in_network;
		}

		function getreplicationfactor(){

			return $this->replication_factor;
		}

		function setreplicationfactor($in_replication_factor){

			$this->replication_factor = $in_replication_factor;
		}

		function getputthroughput(){

			return $this->put_throughput;
		}

		function setputthroughput($in_put_throughput){

			$this->put_throughput = $in_put_throughput;

		}


		function getgettthroughput(){

			return $this->get_throughput;
		}

		function setgettthroughput($in_get_throughput){

			$this->get_throughput = $in_get_throughput;

		}


		function getscanthroughput(){

			return $this->scan_throughput;
		}

		function setscanthroughput($in_scan_throughput){

			$this->scan_throughput = $in_scan_throughput ;

		}

		function getcostdollars(){

			return $this->cost_dollars;
		}

		function setcostdollars($in_cost_dollars){

			$this->cost_dollars=$in_cost_dollars;
		}

		function getjavaheapbytes(){

			return $this->java_heap_bytes;
		}

		function setjavaheapbytes($javaheapbytes){
			$this->java_heap_bytes = $javaheapbytes;
		}

		function getcache(){
			return $this->cache;
		}

		function setcache($in_cache){

			$this->cache = $in_cache;
		}

	}



	/*
	 *  Function initializeConfig: Initialize the Hbase and h/w parameters for the calculation
	*/

	function initializeConfig(){

		global $memstore_flush_size,$reion_size,$block_size,
		$dfs_replication, $region_rs,
		$disk_space, $cost,$raw_hdfs, $hwProfiles;
		;


		// This needs to be picked based on the application usage pattern

		$memstore_flush_size = 256 ; //MBs

		if(!empty($_POST['memstore_size'])){
			$memstore_flush_size = $_POST['memstore_size'];
		}


		$reion_size = 10 ;			 //Gbs

		if(!empty($_POST['region_size'])){
			$reion_size = $_POST['region_size']/1024 ;

		}


		$block_size = 64 ;			 //KBs
		$dfs_replication =3 ;
		$region_rs = 200 ;			// Maximum number of regions per servers

		if(!empty($_POST['regions_per_server'])){
			$region_rs = $_POST['regions_per_server'] ;
		}



		// Hardware configuration : Per region server
		$disk_space = 1024 ;	//Gb
		$cost = 1200 ;			// Dollars
		$raw_hdfs = floor($disk_space/3); //Raw HDFS storage


		//Setting first hw profile - High Memory

		$hwProfile1 = new HWProfile();
		$hwProfile1->setmemoryBtes(137438953472);
		$hwProfile1->setstorageBytes(13194139533312);
		$hwProfile1->setnetwork(1073741824);
		$hwProfile1->setcostdollars(3300);
		$hwProfile1->setcache(95);

		if(!empty($_POST['cache_to_memstore-val'])){
			$hwProfile1->setcache($_POST['cache_to_memstore-val']) ;
		}


		$hwProfile1->setjavaheapbytes(121332826112);

		// PUT through puts
		$putrate1 = new OperationPerSec();
		$putrate1->setRecordSize(0);
		$putrate1->setOpsPerSec(8000);

		$putrate2 = new OperationPerSec();
		$putrate2->setRecordSize(1024);
		$putrate2->setOpsPerSec(6000);

		$putrate3 = new OperationPerSec();
		$putrate3->setRecordSize(10240);
		$putrate3->setOpsPerSec(800);

		$putrate4 = new OperationPerSec();
		$putrate4->setRecordSize(102400);
		$putrate4->setOpsPerSec(100);

		$putrate5 = new OperationPerSec();
		$putrate5->setRecordSize(5242880);
		$putrate5->setOpsPerSec(10);

		$putrate_arr = array($putrate1,$putrate2,$putrate3,$putrate4,$putrate5);

		$hwProfile1->setputthroughput($putrate_arr);

		//GET throughputs
		$getrate1 = new OperationPerSec();
		$getrate1->setRecordSize(0);
		$getrate1->setOpsPerSec(700);

		$getrate2 = new OperationPerSec();
		$getrate2->setRecordSize(1024);
		$getrate2->setOpsPerSec(700);

		$getrate_arr = array($getrate1, $getrate2);
		$hwProfile1->setgettthroughput($getrate_arr);

		//SCAN throughputs
		$scanrate1 = new OperationPerSec();
		$scanrate1->setRecordSize(0);
		$scanrate1->setOpsPerSec(4800);

		$scanrate2 = new OperationPerSec();
		$scanrate2->setRecordSize(1024);
		$scanrate2->setOpsPerSec(4800);

		$scanrate_arr = array($scanrate1, $scanrate2);
		$hwProfile1->setscanthroughput($scanrate_arr);

		//Set to the HW profiles
		//$hwProfiles = array($hwProfile1);




		//Setting second hw profile
		$hwProfile2 = new HWProfile();
		$hwProfile2->setmemoryBtes(68719476736);
		$hwProfile2->setstorageBytes(39582418599936);
		$hwProfile2->setnetwork(1073741824);
		$hwProfile2->setcostdollars(5800);
		$hwProfile2->setcache(70);


		if(!empty($_POST['cache_to_memstore-val'])){
			$hwProfile2->setcache($_POST['cache_to_memstore-val']) ;
		}

		$hwProfile2->setjavaheapbytes(61203283968);

		// PUT through puts
		$putrate1 = new OperationPerSec();
		$putrate1->setRecordSize(0);
		$putrate1->setOpsPerSec(8000);

		$putrate2 = new OperationPerSec();
		$putrate2->setRecordSize(1024);
		$putrate2->setOpsPerSec(6000);

		$putrate3 = new OperationPerSec();
		$putrate3->setRecordSize(10240);
		$putrate3->setOpsPerSec(800);

		$putrate4 = new OperationPerSec();
		$putrate4->setRecordSize(102400);
		$putrate4->setOpsPerSec(100);

		$putrate5 = new OperationPerSec();
		$putrate5->setRecordSize(5242880);
		$putrate5->setOpsPerSec(10);

		$putrate_arr = array($putrate1,$putrate2,$putrate3,$putrate4,$putrate5);

		$hwProfile2->setputthroughput($putrate_arr);

		//GET throughputs
		$getrate1 = new OperationPerSec();
		$getrate1->setRecordSize(0);
		$getrate1->setOpsPerSec(700);

		$getrate2 = new OperationPerSec();
		$getrate2->setRecordSize(1024);
		$getrate2->setOpsPerSec(700);

		$getrate_arr = array($getrate1, $getrate2);
		$hwProfile2->setgettthroughput($getrate_arr);

		//SCAN throughputs
		$scanrate1 = new OperationPerSec();
		$scanrate1->setRecordSize(0);
		$scanrate1->setOpsPerSec(4800);

		$scanrate2 = new OperationPerSec();
		$scanrate2->setRecordSize(1024);
		$scanrate2->setOpsPerSec(4800);

		$scanrate_arr = array($scanrate1, $scanrate2);
		$hwProfile2->setscanthroughput($scanrate_arr);

		//Set to the HW profiles
		$hwProfiles = array($hwProfile1,$hwProfile2);


	}


	/*
	 * Calculate number of region servers based on the storage needs
	*/
	function rsOnStorage($hwprofile){

		global $in_table_storage;

		$in_table_storage = 0;

		//Place Holder keys for table data
		$table_Size = 'Table-Size-';


		//Iterate through the table record sizes to find the Region servers

		$rowIds = $_POST['idvalue'];

		$tok = strtok($rowIds, "|");

		while ($tok !== false) {

			$table_data_size = $_POST[$table_Size.$tok];
			$in_table_storage = $in_table_storage + $table_data_size;
			$tok = strtok("|");
		}

		$raw_hdfs = $hwprofile->getstorageBytes()/3; //Replication factor is 3

		$num_rs_storage = ceil($in_table_storage*1024*1024*1024/$raw_hdfs);



		return $num_rs_storage;

	}

	/*
	 * Calculate number of region servers based on the Regions per server needs
	*/
	function rsOnRegionContraint($hwprofile){

		global $in_table_storage, $reion_size,$region_rs;

		$in_table_storage = 0;

		//Place Holder keys for table data
		$table_Size = 'Table-Size-';


		//Iterate through the table record sizes to find the Region servers

		$rowIds = $_POST['idvalue'];

		$tok = strtok($rowIds, "|");

		while ($tok !== false) {

			$table_data_size = $_POST[$table_Size.$tok];
			$in_table_storage = $in_table_storage + $table_data_size;
			$tok = strtok("|");
		}

		$total_regions_required = ceil($in_table_storage/$reion_size); //Replication of 3

		$total_regions_possible = ceil($hwprofile->getstorageBytes()/($reion_size*1024*1024*1024));
		$region_per_rs = min($total_regions_possible,$region_rs);


		$region_servers_required = ceil($total_regions_required/$region_per_rs);


		return $region_servers_required;

	}

	/*
	 * Calculate the cache size and memstore size based on hot data needs
	*
	* Cache = WSS X Table_size
	* memstore per RS = flush_size X Regions
	* Total memstore = Region Server X memstore per RS
	* Total Heap = Region Server X Heap Size = Total memstore + Cache
	* Region Server = Cache/(Heap - memstore)
	*
	*
	*/

	function calMemToCache($hwprofile){

		global $region_rs, $memstore_flush_size, $cache_needs, $memstore_needs, $memstore_prcnt, $cache_prcnt,
		$hwProfiles, $in_table_storage;


		// Data hot neede for cache across region servers
		$tot_hot_data = 0;

		//Place Holder keys for table data
		$table_Size = 'Table-Size-';
		$hot_data = 'Hot-Data-';

		//Iterate through the table record sizes to find the Region servers

		$rowIds = $_POST['idvalue'];

		$tok = strtok($rowIds, "|");

		while ($tok !== false) {

			$table_data_size = $_POST[$table_Size.$tok];
			$tot_hot_data = $tot_hot_data + $table_data_size*($_POST[$hot_data.$tok])/100;

			$tok = strtok("|");
		}



		$cache_needs = $tot_hot_data; // Gbs

		
		//Check both of the 70:30 and 50:50 configurations for cache to memstore
		$num_region_servers = ceil($cache_needs*1024*1024*1024/($hwprofile->getjavaheapbytes()*$hwprofile->getcache()/100));

		return $num_region_servers;

	}

	/*
	 * This is used by sensitivity Table display charts across cache requirements
	 */	
	
	function getCostForCahe($hwprofile,$cache_alloc){
		
		global $region_rs, $memstore_flush_size, $cache_needs, $memstore_needs, $memstore_prcnt, $cache_prcnt,
		$hwProfiles, $in_table_storage;
		
		
		// Data hot neede for cache across region servers
		$tot_hot_data = 0;
		
		//Place Holder keys for table data
		$table_Size = 'Table-Size-';
		$hot_data = 'Hot-Data-';
		
		//Iterate through the table record sizes to find the Region servers
		
		$rowIds = $_POST['idvalue'];
		
		$tok = strtok($rowIds, "|");
		
		while ($tok !== false) {
		
			$table_data_size = $_POST[$table_Size.$tok];
			$tot_hot_data = $tot_hot_data + $table_data_size*($_POST[$hot_data.$tok])/100;
		
			$tok = strtok("|");
		}
		
		
		$cache_needs = $tot_hot_data; // Gbs
		$num_region_servers = ceil($cache_needs*1024*1024*1024/($hwprofile->getjavaheapbytes()*$cache_alloc/100));
		
		return $num_region_servers;
		
		
	}


	/*
	 * calRSPerformanceNumbers
	* This method claculates the region server based on the H/w performance numbers
	*/
	function calRSPerformanceNumbers($hwprofile){

		global $hwProfiles,$table_avg_record_size;


		//Place Holder keys for table data
		$table_Size = 'Table-Size-';
		$avg_Record_size = 'Avg-Record-size-';
		$hot_Data = 'Hot-Data-';


		//Iterate through the table record sizes to find the Region servers

		$rowIds = $_POST['idvalue'];


		$tok = strtok($rowIds, "|");
		$num_region_servers = 0;

		$table_avg_record_size = $_POST[$avg_Record_size."1"]; //Minimum table record size

		while ($tok !== false) {


			$table_data_size = $_POST[$table_Size.$tok];
			$table_hot_data = $_POST[$hot_Data.$tok];

			if($_POST[$avg_Record_size.$tok] < $table_avg_record_size){
					
				$table_avg_record_size = $_POST[$avg_Record_size.$tok];
					
			}
			$tok = strtok("|");
		}

		$read_rate = $_POST['read-rate'];
		$write_rate = $_POST['write-rate'];
		$scan_rate = $_POST['scan-rate'];

		if($scan_rate==0){
			$scan_rate = 1;
		}

		$num_region_servers = $num_region_servers +
		max(getRSforPuts($table_avg_record_size,$write_rate,$hwprofile)
				,getRSforGets($table_avg_record_size,$read_rate,$hwprofile)
				,getRSforScans($table_avg_record_size,$scan_rate,$hwprofile));


		return $num_region_servers;

	}

	function getRSforPuts($in_record_size,$write_rate,$hwProfile){
		global $num_rs_wr_1,$num_rs_wr_2,$puts_per_sec;

		$putrate_arr = $hwProfile->getputthroughput();

		foreach ($putrate_arr as $putrate){

			if($in_record_size<$putrate->getRecordsize()){
					
				$puts_per_sec = $write_rate;

				if($num_rs_wr_1 == 0){
					$num_rs_wr_1 = ceil($puts_per_sec/$putrate->getOpsPerSec());
				}else{
					$num_rs_wr_2 = ceil($puts_per_sec/$putrate->getOpsPerSec());
				}
					
				return ceil($puts_per_sec/$putrate->getOpsPerSec());

			}
		}


	}

	function getRSforGets($in_record_size,$read_rate,$hwProfile){

		global $num_rs_rr_1,$num_rs_rr_2,$gets_per_sec,$hwreadrate_1,$hwreadrate_2;

		$getrate_arr = $hwProfile->getgettthroughput();

		foreach ($getrate_arr as $getrate){


			if($in_record_size<$getrate->getRecordsize()){

				$gets_per_sec = $read_rate;

				if($num_rs_rr_1 == 0){
					$num_rs_rr_1 = ceil($gets_per_sec/$getrate->getOpsPerSec());
					$hwreadrate_1 = $getrate->getOpsPerSec();
				}else{
					$num_rs_rr_2 = ceil($gets_per_sec/$getrate->getOpsPerSec());
					$hwreadrate_2 = $getrate->getOpsPerSec();
				}
				return ceil($gets_per_sec/$getrate->getOpsPerSec());

			}
		}


	}

	function getRSforScans($in_record_size,$scan_rate,$hwProfile){
		global $num_rs_sr_1,$num_rs_sr_2,$scan_per_sec;

		$scanrate_arr = $hwProfile->getscanthroughput();

		foreach ($scanrate_arr as $scanrate){


			if($in_record_size<$scanrate->getRecordsize()){

				$scan_per_sec = $scan_rate ;

				if($num_rs_sr_1 == 0){
					$num_rs_sr_1 = ceil($scan_per_sec/$scanrate->getOpsPerSec());
				}else{
					$num_rs_sr_2 = ceil($scan_per_sec/$scanrate->getOpsPerSec());

				}

				return ceil($scan_per_sec/$scanrate->getOpsPerSec());

			}
		}

	}

	/*
	 * Paint the sensitivity graph for the bottle neck
	*/

	function addGraph($hwprofile){




	}


	/*
	 * initialize the parameters before the calculation
	*/
	initializeConfig();




	// Step 1: Calculate number of region servers based on the storage needs


	// Step 1.1: Based on the cache overhead -- To be done

	$num_rs_storage_1 = rsOnStorage($hwProfiles[0]);
	$num_rs_storage_2 = rsOnStorage($hwProfiles[1]);


	$num_rs_performance_1 = calRSPerformanceNumbers($hwProfiles[0]);
	$num_rs_performance_2 = calRSPerformanceNumbers($hwProfiles[1]);

	$num_rs_cache_1 = calMemToCache($hwProfiles[0]);
	$num_rs_cache_2 = calMemToCache($hwProfiles[1]);

	$num_rs_regionserver_1 = rsOnRegionContraint($hwProfiles[0]);
	$num_rs_regionserver_2 = rsOnRegionContraint($hwProfiles[1]);


	$max_region_servers_1 = max($num_rs_storage_1,$num_rs_performance_1,$num_rs_cache_1,$num_rs_regionserver_1,1);
	$bottleneck_1 = "NONE";
	
	if($max_region_servers_1==$num_rs_storage_1){
		$bottleneck_1 = "DISK STORAGE";
	}
	if($max_region_servers_1==$num_rs_performance_1){
		$bottleneck_1 = "READ/WRITE PERFORMANCE";
	}
	if($max_region_servers_1==$num_rs_cache_1){
		$bottleneck_1 = "CACHE REQUIREMENT";
	}
	if($max_region_servers_1==$num_rs_regionserver_1){
		$bottleneck_1 = "REGIONS PER SERVER";
	}
	



	//Total cost of owning the hardware
	$Total_cost_1 = $max_region_servers_1 * $hwProfiles[0]->getcostdollars() ;

	$max_region_servers_2 = max($num_rs_storage_2,$num_rs_performance_2,$num_rs_cache_2,$num_rs_regionserver_2,1);
	$bottleneck_2 = "NONE";
	
	if($max_region_servers_2==$num_rs_storage_2){
		$bottleneck_2 = "DISK STORAGE";
	}
	if($max_region_servers_2==$num_rs_performance_2){
		$bottleneck_2 = "READ/WRITE PERFORMANCE";
	}
	if($max_region_servers_2==$num_rs_cache_2){
		$bottleneck_2 = "CACHE REQUIREMENT";
	}
	if($max_region_servers_2==$num_rs_regionserver_2){
		$bottleneck_2 = "REGIONS PER SERVER";
	}
	
	

	//Total cost of owning the hardware
	$Total_cost_2 = $max_region_servers_2 * $hwProfiles[1]->getcostdollars() ;
	
	
	$best_hwprofile;
	$hwreadrate;
	$bottleneck;
	if($Total_cost_1<$Total_cost_2){
		$best_hwprofile = $hwProfiles[0];
		$hwreadrate = $hwreadrate_1;
		$bottleneck = $bottleneck_1;
		
	}else{
		$best_hwprofile = $hwProfiles[1];
		$hwreadrate = $hwreadrate_2;
		$bottleneck = $bottleneck_2;
	}




	?>
	
<form  class="pure-form pure-form-stacked" >
	
<h1>HBase Calculator : Results</h1>

<fieldset>
	
	<legend> Calculations are based on performance runs conducted on the HBase hardware configuration displayed below. </legend>
	<div class="l-content">
		<div class="pricing-tables pure-g-r">
			<div class="pure-u-1-3">
				<div class="pricing-table pricing-table-biz pricing-table-selected">
					<div class="pricing-table-header">
						<h2>Hardware Config</h2>
					</div>

					<ul class="pricing-table-list">
						<li>Storage: <b><?php 
						if($Total_cost_1<$Total_cost_2){
										echo("4 x 3TB SATA");
									}else{
										echo("12 x 3TB SATA");
									}

									?>
						</b>
						</li>

						<li>Memory: <b><?php 									
						if($Total_cost_1<$Total_cost_2){
										echo("128GB 1333MHz DDR3");
									}else{
										echo("64GB 1333MHz DDR3");
									}
									?>
						</b>
						</li>
						<li>Java Heap: <b><?php 									
						if($Total_cost_1<$Total_cost_2){
							echo("113 Gb");
						}else{
							echo("57 Gb");
									}
									?>
						</b>
						</li>
						<li>Cost per Server: <b><?php 									
						if($Total_cost_1<$Total_cost_2){
							echo("$ 3,800");
						}else{
							echo("$ 5,500");
									}
									?>
						</b>
						</li>
					</ul>

				</div>
			</div>
						<div class="pure-u-1-3">
				<div class="pricing-table pricing-table-biz pricing-table-selected">
				
					<div class="pricing-table-header">
						<h2>HBase Config</h2>

				</div>

					<ul class="pricing-table-list">
						<li>Region Size: <b><?php echo($reion_size);?> Gb</b></li>
						<li>Regions Per Server:	<b><?php echo($region_rs);?></b></li>
						<li>Cache Allocation:	<b><?php echo($_POST['cache_to_memstore-val'])?> %</b></li>
						<li>Memstore Size:	<b><?php echo($_POST['memstore_size'])?> Mb</b></li>
					</ul>

				</div>
			</div>
			<div class="pure-u-1-3">
				<div class="pricing-table pricing-table-free">
					<div class="pricing-table-header">
						<h2>Project Cost</h2>

					</div>

					<ul class="pricing-table-list">
						<li>Estimated Total Cost: <b>
							$
							<?php 
							if($Total_cost_1<$Total_cost_2){
								echo(number_format($Total_cost_1, 0));
							}else{
								echo(number_format($Total_cost_2, 0));
							}
							?></b>

						</li>
						<li>Estimated Region Server Count: <b><?php 
						
						if($Total_cost_1<$Total_cost_2){
							echo(number_format($max_region_servers_1, 0));
						}else{
							echo(number_format($max_region_servers_2, 0));
						}
						
						?>
						</b>
						</li>
						<li> Bottleneck: <b><?php echo($bottleneck);?></b>
						
						</li>
											<li> <br>
						
						</li>
	
					</ul>

				</div>
			</div>


		</div>
	</div>
	<!-- end pricing-tables -->
</fieldset>

<br>
<br>
</form>

</body>
</html>
