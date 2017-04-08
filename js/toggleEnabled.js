/*  Updates submission form fields based on changes in the category
*   dropdown.
*/
var LOCtoggleEnabled = function(ck, id, type) {
    oldval = ck.checked ? 0 : 1;
     var dataS = {
        "action" : "toggle",
        "id": id,
        "oldval": oldval,
        "type": type,
    };
    //data = $("form").serialize() + "&" + $.param(dataS);
    data = $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_admin_url + "/plugins/locator/ajax.php",
        data: data,
        success: function(result) {
            // No change to form content, just display a message
            try {
                $.UIkit.notify("<i class='uk-icon-check'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:'top-center'});
            }
            catch(err) {
                // Form is already updated, annoying popup message not needed
                // alert(result.statusMessage);
            }
        }
    });
    return false;
};
