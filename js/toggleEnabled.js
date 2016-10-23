/*  Updates submission form fields based on changes in the category
*  dropdown.
*/
var LOCxmlHttp;

function LOCtoggleEnabled(ck, id, type)
{
  if (ck.checked) {
    newval=1;
    oldval=0;
  } else {
    newval=0;
    oldval=1;
  }

  LOCxmlHttp=LOCGetXmlHttpObject();
  if (LOCxmlHttp==null) {
    alert ("Browser does not support HTTP Request")
    return
  }
  var url=glfusionSiteUrl + "/admin/plugins/locator/ajax.php?action=toggle";
  url=url+"&id="+id;
  url=url+"&type="+type;
  url=url+"&oldval="+oldval;
  url=url+"&sid="+Math.random();
  LOCxmlHttp.onreadystatechange=LOCsc_toggleEnabled;
  LOCxmlHttp.open("GET",url,true);
  LOCxmlHttp.send(null);
}

function LOCsc_toggleEnabled()
{
  var newstate;

  if (LOCxmlHttp.readyState==4 || LOCxmlHttp.readyState=="complete") {
    jsonObj = JSON.parse(LOCxmlHttp.responseText);
    id = jsonObj.id;
    baseurl = jsonObj.baseurl;
    type = jsonObj.type;
    newval = jsonObj.newval;
    document.getElementById(type+"_"+id).checked = jsonObj.newval == 1 ? true : false;
  }
}

function LOCGetXmlHttpObject()
{
  var objXMLHttp=null
  if (window.XMLHttpRequest) {
    objXMLHttp=new XMLHttpRequest()
  } else if (window.ActiveXObject) {
    objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP")
  }
  return objXMLHttp
}

