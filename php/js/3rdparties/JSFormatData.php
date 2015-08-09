<script language="javascript">

function y2k(number) { 
  return (number < 1000) ? number + 1900 : number; 
}

function verData () {

    var today = new Date();
    yyyy = ((!yyyy) ? y2k(today.getYear()):yyyy);
    mm = ((!mm) ? today.getMonth():mm-1);
    if (!gg) return false
    var test = new Date(yyyy,mm,gg);
    if ( (y2k(test.getYear()) == yyyy) &&
         (mm == test.getMonth()) && (gg == test.getDate()) )

        return true; 
    else
        return false;

}

function trim(s) {
return s.replace( /^\s*/, "" ).replace( /\s*$/, "" );
}

function formatData(sdata) {
var 	aa=0
var stringa=" "

 	if (sdata=="") 
	  return("");
	sdata=trim(sdata);
	if( sdata.length == 10 ) {
		gg=sdata.substring(0,2);
                mm=sdata.substring(3,5);
                yyyy=sdata.substring(6,10);
		stringa=gg+"/"+mm+"/"+yyyy;
                if (verData())
                        return(sdata);
                else
                        return("data errata");	
	}
	if(sdata.length == 6 ) {
		aa = sdata.substring(4,6)
		if( aa < 50)
			anno="20";
		else
			anno="19";
		gg=sdata.substring(0,2);
		mm=sdata.substring(2,4);
		yyyy=anno+sdata.substring(4,6);
		stringa=gg+"/"+mm+"/"+yyyy;
		if (verData())
			return(stringa);
		else 
			return("data errata");

	}
	if(sdata.length == 8) {
		gg=sdata.substring(0,2);
                mm=sdata.substring(2,4);
                yyyy=sdata.substring(4,8);
                stringa=gg+"/"+mm+"/"+yyyy;
                if (verData())
                        return(stringa);
                else
                        return("data errata");
	
	}
	return("data errata");
}
</script>
