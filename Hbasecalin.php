<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>HBase Calculator &ndash; Pure</title>


<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.2.1/pure-min.css">

<link rel="stylesheet" href="http://purecss.io/combo/1.5.4?/css/layouts/pricing.css">

    
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

function updateTextInput(scrollBarId){
	var scrollBarValId = scrollBarId+'-val';
	document.getElementById(scrollBarValId).value=document.getElementById(scrollBarId).value;	
}


function prepareData(){
	var table = document.getElementById('HBaseTbl');
	var indexes = '';     
    var r=0;

    while(row=table.rows[r++])
    {    	
    	var cell = row.cells[0];
    	indexes = indexes +'|'+ cell.innerHTML;
	}
    document.getElementById('idvalue').value= indexes;

    
}

function addHWConfig(storagevalue,memoryvalue,regionsize,regionrs,javaheap,cacheprct){

	    document.getElementById('storagevalue').value= storagevalue;
	    document.getElementById('memoryvalue').value= memoryvalue;
	    document.getElementById('regionsize').value= regionsize;
	    document.getElementById('regionrs').value= regionrs;
	    document.getElementById('javaheap').value= javaheap;
	    document.getElementById('cacheprct').value= cacheprct;
	    
	    
}

function addRow(tableID) {               

    var table = document.getElementById(tableID);               
    var rowCount = table.rows.length;             
    var row = table.insertRow(rowCount);
    var currCount = rowCount + 1;
                   
    var cell1 = row.insertCell(0);
    cell1.innerHTML = currCount;
    
    var cell2 = row.insertCell(1);             
    cell2.innerHTML = "<label for=\"Table-Size-"+currCount+"\">Total Data Size (Gb)</label><input id=\"Table-Size-"+currCount+"\" name=\"Table-Size-"+currCount+"\" type=\"number\" min=\"0\" required=\"required\">";
    
    var cell3 = row.insertCell(2);
    cell3.innerHTML = "<label for=\"Avg-Record-size-"+currCount+"\">Average Record Size (Bytes)</label><input id=\"Avg-Record-size-"+currCount+"\" name=\"Avg-Record-size-"+currCount+"\" type=\"number\" width=\"1\" min=\"1\" required=\"required\">";               
    

    var cell4 = row.insertCell(3);             
    cell4.innerHTML = "<label for=\"Hot-Data-"+currCount+"\">% of Data actively read</label><input id=\"Hot-Data-"+currCount+"\" name=\"Hot-Data-"+currCount+"\" type=\"range\" min=\"1\" max=\"100\"  value=\"30\" onchange=\"updateTextInput('Hot-Data-"+currCount+"');\" required=\"required\"><input id=\"Hot-Data-"+currCount+"-val\" name=\"Hot-Data-"+currCount+"-val\" readonly=\"readonly\" size=\"1\" style=\"border:0px;\" value=\"30\">%";

    var cell5 = row.insertCell(4);
    //cell5.innerHTML = "<input type=\"button\" onclick=\"deleteRow('HBaseTbl','"+rowCount+"')\" value=\"-\" class=\"pure-button-error\">" ;

    cell5.innerHTML = "<input type=\"button\" value=\"-\" onclick=\"deleteRow(this);\"class=\"pure-button-error\">";
}


function deleteRow(o) {
	   var p=o.parentNode.parentNode;
       p.parentNode.removeChild(p);

}

</script>
</head>

<body>
<form name="hbaseinput" class="pure-form pure-form-stacked" action="Hbasecalres.php" method="post">


<h1>HBase Calculator</h1>

	<fieldset>
        <legend>Step 1. Enter Table Details</legend>

<div>
    <style scoped>
        .pure-button-success,
        .pure-button-error,
        .pure-button-warning,
        .pure-button-secondary {
            color: white;
            border-radius: 4px;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
        }

        .pure-button-success {
            background: rgb(28, 184, 65); /* this is a green */
        }

        .pure-button-error {
            background: rgb(202, 60, 60); /* this is a maroon */
            font-size: 70%;
        }

        .pure-button-warning {
            background: rgb(223, 117, 20); /* this is an orange */
        }

        .pure-button-secondary {
            background: rgb(66, 184, 221); /* this is a light blue */
        }

    </style>

    <button class="pure-button pure-button-secondary" onclick="addRow('HBaseTbl');">Add Table</button>
</div>        
        
<table  width='90%' border="0" id="HBaseTbl">

    <tbody>
        <tr>
            <td>1</td>
            <td width='30%'>
	                <label for="Table-Size-1" title="Size of the dataset for the table in Gbs">Total Data Size (Gb)
	                <a href="#" 
	                onclick="window.open('http://wiki.corp.yahoo.com/view/Grid/HbaseCal#Table_Size','popUpWindow','height=500,width=400,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');"
	                >(?)</a>
	                </label>
	                
	                <input id="Table-Size-1" name="Table-Size-1" type="number" min="0" required="required">
            </td>
            <td width='30%'>
	                <label for="Avg-Record-size-1">Average Record Size (Bytes)
	                
	                <a href="#" 
	                onclick="window.open('http://wiki.corp.yahoo.com/view/Grid/HbaseCal#Average_Record_Size','popUpWindow','height=500,width=400,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');"
	                >(?)</a>
	                </label>
	                <input id="Avg-Record-size-1" name="Avg-Record-size-1" type="number" width="1" min="1" required="required">
            </td>
            <td width='30%'>
                <label for="Hot-Data-1">% Of Data Cached
                <a href="#" 
	                onclick="window.open('http://wiki.corp.yahoo.com/view/Grid/HbaseCal#A_37_Of_Data_Cached','popUpWindow','height=500,width=400,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');"
	                >(?)</a>
                </label>
                <input id="Hot-Data-1" name="Hot-Data-1" type="range" min="1" max="100"  value="30" onchange="updateTextInput('Hot-Data-1');" required="required">
                <input id="Hot-Data-1-val" name="Hot-Data-1-val" readonly="readonly" size="1" style="border:0px;" value="30">%            
            </td>
            <td width='3%'>
                        	     
            </td>
        </tr>
		
        </tbody>
</table>

</fieldset>

<br>
<br>

<fieldset>
        <legend>Step 2. Enter Expected Performance Details</legend>
<table  width='90%' border="0" id="HBaseTbl">

    <tbody>
        <tr>
            <td></td>
            <td width='30%'>
               <label for="read-rate">Expected Reads Per Second
               <a href="#" 
	                onclick="window.open('http://wiki.corp.yahoo.com/view/Grid/HbaseCal#Expected_Reads_Per_Second','popUpWindow','height=500,width=400,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');"
	                >(?)</a>
               </label>
                <input id="read-rate" name="read-rate" type="number" min="1" step="1"  required="required">
            </td>
            <td width='30%'>
               <label for="write-rate">Expected Writes Per Second
               <a href="#" 
	                onclick="window.open('http://wiki.corp.yahoo.com/view/Grid/HbaseCal#Expected_Writes_Per_Second','popUpWindow','height=500,width=400,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');"
	                >(?)</a>
               </label>
                <input id="write-rate" name="write-rate" type="number" min="1" step="1" required="required">
            </td>
            <td width='30%'>
               <label for="scan-rate">Expected Scans Per Second (Optional)
               <a href="#" 
	                onclick="window.open('http://wiki.corp.yahoo.com/view/Grid/HbaseCal#Expected_Scans_Per_Second','popUpWindow','height=500,width=400,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');"
	                >(?)</a>
               </label>
                <input id="scan-rate" name="scan-rate" type="number" min="1" step="1">
            
            </td>
            <td width='3%'>
                        	     
            </td>
        </tr>
		
        </tbody>
</table>
        
 
 </fieldset>
 <br>
  <legend> Step 3. Default HBase Configuration (disabled in this release) </legend>
  
  <table  width='90%' border="0" id="HBaseTbl">

    <tbody>
        <tr>
            <td></td>
            <td width='30%'>
		            <label for="region_size">Region Size (Mb)</label>
		            <input id="region_size" name="region_size" type="number" placeholder="region size" min="1" value="10240" readonly="readonly" >
		     </td>
            <td width='30%'>
               	<label for="regions_per_server">Regions Per Server</label>
            	<input id="regions_per_server" name="regions_per_server" type="number" placeholder="Regions Per Server" min="20" value="200" readonly="readonly">
            </td>
            <td width='30%'>               
            </td>
            <td width='3%'>                     	     
            </td>
        </tr>
        <tr>
            <td></td>
            <td width='30%'>
                
                <label for="cache_to_memstore">Cache To Memstore</label>
                <input id="cache_to_memstore" name="cache_to_memstore" type="range" min="10" max="95"  value="70" onchange="updateTextInput('cache_to_memstore');" readonly="readonly">
                <input id="cache_to_memstore-val" name="cache_to_memstore-val" readonly="readonly" size="1" style="border:0px;" value="70">%            
 		     </td>
            <td width='30%'>
               	<label for="memstore_size">Memstore Size (Mb)</label>
            	<input id="memstore_size" type="number" name="memstore_size" placeholder="Memstore Flush Size" value="256" readonly="readonly">
            </td>
            <td width='30%'>
                           
            </td>
            <td width='3%'>                     	     
            </td>
        </tr>
        <tr>
            <td></td>
            <td width='30%'>
                       <label for="compression_used">Compression Used</label>
                <select id="compression_used" class="pure-input-1-2" readonly="readonly">
                    <option>None</option>
                    <option>LZO</option>
                </select>
 
		     </td>
            <td width='30%'>
            </td>
            <td width='30%'>
                           
            </td>
            <td width='3%'>                     	     
            </td>
        </tr>
		<tr>
            <td></td>
            <td width='30%'>
 
		     </td>
            <td width='30%'>
            </td>
            <td width='30%'>
            <button type="submit" onclick="prepareData()"  class="pure-button pure-button-primary">Calculate</button>               
            </td>
            <td width='3%'>  
                               	     
            </td>
        </tr>
		        
		
        </tbody>
</table>

 
      
 <br><br>
 <br>

 <br><br>
                 
 
 <input type="hidden" id="idvalue" name="idvalue" >
 <input type="hidden" id="storagevalue" name="storagevalue" value="12">
 <input type="hidden" id="memoryvalue" name="memoryvalue" value="128">
 <input type="hidden" id="regionsize" name="regionsize" value="100">
 <input type="hidden" id="regionrs" name="regionrs" value="200">
 <input type="hidden" id="javaheap" name="javaheap" value="113">
 <input type="hidden" id="cacheprct" name="cacheprct" value="50">
 
 
 
 
</form>

</body>

</html>
