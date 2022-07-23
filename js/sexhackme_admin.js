jQuery(document).ready(function($) {
    $("#sh_admin_tabs .hidden").removeClass('hidden');
    $("#sh_admin_tabs").tabs({'active': 0});


  // ::: TAGS BOX
  $("#shvtags input").on({
    focusout : function() {
      var txt = this.value.replace(/[^a-z0-9\+\-\.\#]/ig,''); // allowed characters
      if(txt) {
       $("<span/>", {text:txt.toLowerCase(), insertBefore:this});
       $("<input>").attr( {type:"hidden", 
                          name:"video_tags[]", 
                          value:txt.toLowerCase(),
                          data:txt.toLowerCase(),
                  }).appendTo('#vtagsdata');
      }
      this.value = "";
    },
    keydown : function(ev) {
      if(/(13)/.test(ev.which)) {
         // Prevent enter key to send the form
         ev.preventDefault();
         return false;
      }
    },
    keyup : function(ev) {
      // if: comma|enter (delimit more keyCodes with | pipe)
      if(/(188|13)/.test(ev.which)) {
         $(this).focusout(); 
      }
    }
  });
  $('#shvtags').on('click', 'span', function() {
    if(confirm("Remove "+ $(this).text() +"?")) {
       var txt=$(this).text();
       $("input[name='video_tags[]'][data='"+txt+"']").remove();
       $(this).remove(); 

    }
  }); 

});
