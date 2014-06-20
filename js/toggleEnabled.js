/*  Updates submission form fields based on changes in the category
*  dropdown.
*/
var LOCxmlHttp;

function LOCtoggleEnabled(ck, id, type, base_url)
{
  if (ck.checked) {
    newval=1;
  } else {
    newval=0;
  }

  LOCxmlHttp=LOCGetXmlHttpObject();
  if (LOCxmlHttp==null) {
    alert ("Browser does not support HTTP Request")
    return
  }
  var url=base_url + "/admin/plugins/locator/ajax.php?action=toggleEnabled";
  url=url+"&id="+id;
  url=url+"&type="+type;
  url=url+"&newval="+newval;
  url=url+"&sid="+Math.random();
  LOCxmlHttp.onreadystatechange=LOCsc_toggleEnabled;
  LOCxmlHttp.open("GET",url,true);
  LOCxmlHttp.send(null);
}

function LOCsc_toggleEnabled()
{
  var newstate;

  if (LOCxmlHttp.readyState==4 || LOCxmlHttp.readyState=="complete")
  {
    xmlDoc=LOCxmlHttp.responseXML;
    id = xmlDoc.getElementsByTagName("id")[0].childNodes[0].nodeValue;
    //imgurl = xmlDoc.getElementsByTagName("imgurl")[0].childNodes[0].nodeValue;
    baseurl = xmlDoc.getElementsByTagName("baseurl")[0].childNodes[0].nodeValue;
    type = xmlDoc.getElementsByTagName("type")[0].childNodes[0].nodeValue;
    if (xmlDoc.getElementsByTagName("newval")[0].childNodes[0].nodeValue == 1) {
        checked = "checked";
        newval = 0;
    } else {
        checked = "";
        newval = 1;
    }
    document.getElementsByName(type+"_"+id).checked = checked;
    
  }

}

function LOCGetXmlHttpObject()
{
  var objXMLHttp=null
  if (window.XMLHttpRequest)
  {
    objXMLHttp=new XMLHttpRequest()
  }
  else if (window.ActiveXObject)
  {
    objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP")
  }
  return objXMLHttp
}

