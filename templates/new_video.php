<?php
/**
 * Copyright: 2022 (c)Franco (nextime) Lanza <franco@nexlab.it>
 * License: GNU/GPL version 3.0
 *
 * This file is part of SexHackMe Wordpress Plugin.
 *
 * SexHackMe Wordpress Plugin is free software: you can redistribute it and/or modify it 
 * under the terms of the GNU General Public License as published 
 * by the Free Software Foundation, either version 3 of the License, 
 * or (at your option) any later version.
 *
 * SexHackMe Wordpress Plugin is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License 
 * along with SexHackMe Wordpress Plugin. If not, see <https://www.gnu.org/licenses/>.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


$video = new \wp_SexHackMe\SH_Video();

$uniqid = uniqid();

// XXX BUG Check better the form for guest, it makes soooo much shit

?>

   <script type="text/javascript">

   window.guestChange = function(trig)
   {
      if(trig.value > 0)
      {
         var newsel = $('.guest_selection').clone();
         var vgar = $('.guest_list p select[name="vguests[]"]').find(':selected').map(function() {return $(this).val()}).get().slice(1);
         console.log(vgar);
         if($.inArray(0, vgar)===-1) {
            newsel.insertAfter($('.guest_list p').last());
            newsel.show();
            newsel.removeClass('guest_selection');
         } 

      } else {
         $('.guest_list p').last().remove();
         //$('.guest_list p').last().find('select').prop('disabled', false);
      }
   }

   </script>

   <p>
      <h4>Title:</h4>
      <input type="text" style="width:100%" name="post_title" size="30" placeholder="Video title"  value="" id="title" spellcheck="true" autocomplete="off" />
      <input type="hidden" name="uniqid"  value="<?php echo $uniqid ?>" />
   </p>
   <p>
      <h4>Description:</h4>
      <textarea style="width:100%" id="video_description" name="video_description" placeholder="Video description"></textarea>
   </p>
   <p>
      <h4>Privacy:</h4>
      <p>
         <label> * Show video in public gallery?</label>
         <input type='radio' name='video_visible' value='Y' <?php if($video->visible=='Y') echo "checked"; ?>>Yes</input>
         <input type='radio' name='video_visible' value='N' <?php if($video->visible=='N') echo "checked"; ?>>No</input>
      </p>
      <p>
         <label> * Show video in profile gallery?</label>
         <input type='radio' name='video_private' value='N' <?php if($video->private=='N') echo "checked"; ?>>Yes</input>
         <input type='radio' name='video_private' value='Y' <?php if($video->private=='Y') echo "checked"; ?>>No</input>
      </p>
   </p>
   <p>
   <p>
      <h4>Virtual Reality</h4>
      <p>
         <label> * VR Video?:</label>
         <input type='radio' name='video_type' value='VR' <?php if($video->video_type=='VR') echo "checked"; ?>>Yes</input>
         <input type='radio' name='video_type' value='FLAT' <?php if($video->video_type=='FLAT') echo "checked"; ?>>No</input>
      </p>
      <p>
         <label> * VR Projection</label>
         <select name='video_vr_projection' id='video_vr_projection'>
            <option value='VR180_LR' <?php if($video->vr_projection=='VR180_LR') echo "selected"; ?>>Equirectangular 180 LR</option>
            <option value='VR360_LR' <?php if($video->vr_projection=='VR360_LR') echo "selected"; ?>>Equirectangular 360 LR</option>
         </select>
         <label>(ignored for non VR videos)</option>
      </p>
   </p>
   <p>
      <h4>Download Price:</h4>
      <label>USD:</label>
      <input type='text' name="video_price" value='<?php  echo esc_attr( $video->price ); ?>' />
   </p>


<!-- (A) UPLOAD BUTTON & LIST -->
<?php 
foreach(array('public','members','premium') as $level) { ?>
<p>
   <h4><?php echo ucfirst($level); ?> Video:</h4>
<form>
  <div id="dropvideo_<?php echo $level; ?>"  style="text-align:center;padding: 25px; border: 1px solid #534b4b;background: #232121;">DROP FILE HERE</div>
  <div id="newvideo_<?php echo $level; ?>_list"></div>
  <input type="button" id="upBrowse_<?php echo $level; ?>" value="Browse">
  <input type="button" id="upToggle_<?php echo $level; ?>" value="Pause OR Continue">
  <input type="button" id="delToggle_<?php echo $level; ?>" value="Cancel">
  <input type="hidden" name="filename_<?php echo $level; ?>" value="">
</form>

      <p>
         <label> Include in Download?</label>
         <input type='radio' name='video_isdownload_<?php echo $level; ?>' value='Y' <?php if($video->has_downloads($level)) echo "checked"; ?>>Yes</input>
         <input type='radio' name='video_isdownload <?php echo $level; ?>' value='N' <?php if(!$video->has_downloads($level)) echo "checked"; ?>>No</input>
      </p>
</p>
<?php 
	} 

$cats = \wp_SexHackMe\sh_get_categories();
?>
<p>
   <h4>Categories:</h4>
   <div class="wrap">
         <table class="form-table" id="catstable">
                  <?php
							$ct=0;
                     foreach($cats as $cat)
                     {
								if($ct == 0) echo "<tr align=\"top\">";
								elseif($ct % 5 == 0) echo "</tr><tr align=\"top\">";
								echo "<td>";
                        echo "<p><input type='checkbox' name='vcategory[]' value='".$cat->id."' ";
                        if($video->has_category($cat->id)) echo "checked />";
                        echo "<label>".$cat->category."</label></p>\n";
								echo "</td>";
								$ct+=1;
                     }
              			echo "</tr>";
                  ?>
         </table>
   </div>
</p>


<p>
   <h4>Models:</h4>

   <div class="wrap">
         <table class="form-table">
<?php 
      $models = get_users( array( 'role__in' => array( 'model' ) ) );
      /*
            <tr align="top">
               <td>
                  <p><label>Select Model user</label></p>
                  <?php // XXX When this will be with thousands of model will definely not scale! ?>
                  <select name='video_model'>
                  <?php
                     $models = get_users( array( 'role__in' => array( 'model' ) ) );
                     foreach($models as $user)
                     {
                        echo "<option value='".$user->ID."' ";
                        if($video->user_id==$user->ID) echo "selected";
                        echo '>'.$user->user_login." (id:".$user->ID.")</option>";
                     } ?>
                  </select>
               </td>
            </tr>
*/ ?>
            <tr align="top">
               <td class='guest_list'>
                  <p style="display:none" class="guest_selection">
                     <select name='vguests[]' onchange='javascript:guestChange(this);'>
                        <option value="0">NO GUEST</option>
                        <?php
                        foreach($models as $user)
                        {
                           echo "<option value='".$user->ID."' ";
                           echo '>'.$user->user_login." (id:".$user->ID.")</option>";
                        }
                        ?>
                     </select>
                  </p>
                  <p>
                     <label>Add guest model</label>
                     <?php // XXX When this will be with thousands of model will definely not scale! ?>
                  </p>
                  <p>
                     <select name='vguests[]' onchange='javascript:guestChange(this);'>
                        <option value="0">NO GUEST</option>
                        <?php
                        foreach($models as $user)
                        {
                           echo "<option value='".$user->ID."' ";
                           echo '>'.$user->user_login." (id:".$user->ID.")</option>";
                        } ?>
                     </select>
                  </p>
                  <?php
                     foreach($video->get_guests(true) as $uid => $guest)
                     {
                        ?>
                  <p>
                     <select name='vguests[]' onchange='javascript:guestChange(this);'>
                        <option value="0">NO GUEST</option>
                        <?php
                        foreach($models as $user)
                        {
                           echo "<option value='".$user->ID."' ";
                           if($uid==$user->ID) echo "selected";
                           echo '>'.$user->user_login." (id:".$user->ID.")</option>";
                        } ?>
                     </select>
                  </p>
                        <?php
                     }
                  ?>
               </td>
            </tr>

         </table>
   </div>

<p>
	<h4>Tags:</h4>
<div id="shvtags">
<?php
	foreach($video->get_tags() as $tag)
	{
   	echo "  <span>".$tag->tag."</span>\n";
	}  
?>
  <input type="text" value="" placeholder="Add a tag" />
</div>
</p>
<p>
<div width="100%">
<br><br><br>
<p class="howto" id="new-tag-video_tags-desc">Insert tag, confirm with enter or comma</p>
</div>
</p>
<div id="vtagsdata"></div>
<?php
	foreach($video->get_tags() as $tag)
	{
   	echo "  <input type='hidden' name='video_tags[]' data='".$tag->tag."'  value='".$tag->tag."' />\n";
	}

$titlear = array(
   'preview' => 'Preview video',
   'thumb' => 'Thumbnail image',
   'gif' => 'GIF Video preview',
   'gif_small' => 'Small GIF for thumbnail preview');

foreach(array('thumb','gif_small','gif','preview') as $imgt) {

?>

<p>
   <h4><?php echo $titlear[$imgt]; if($imgt=='thumb') echo " (Strongly suggested)" ?>:</h4>
<form>
  <div id="dropvideo_<?php echo $imgt; ?>" style="text-align:center;padding: 25px; border: 1px solid #534b4b;background: #232121;">DROP FILE HERE</div>
  <div id="newvideo_<?php echo $imgt; ?>_list"></div>
  <input type="button" id="upBrowse_<?php echo $imgt; ?>" value="Browse">
  <input type="button" id="upToggle_<?php echo $imgt; ?>" value="Pause OR Continue">
  <input type="button" id="delToggle_<?php echo $imgt; ?>" value="Cancel">
  <input type="hidden" name="filename_<?php echo $imgt; ?>" value="">
</form>
</p>

<?php } ?>

<p>
   <div style="align:center;text-align:center">
      <input disabled type="button" id="send" value="Save Video">
   </div>
</p>

<!-- (B) LOAD FLOWJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/flow.js/2.14.1/flow.min.js"></script>
<script>
// (C) INIT FLOWJS
jQuery(function($) {

    //$("#sh_admin_tabs .hidden").removeClass('hidden');
    //$("#sh_admin_tabs").tabs({'active': 0});


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
    //if(confirm("Remove "+ $(this).text() +"?")) {
       var txt=$(this).text();
       $("input[name='video_tags[]'][data='"+txt+"']").remove();
       $(this).remove();
    //}
  });

  $('#send').on('click', function() {
     console.log('uhmm');
     formdata = new FormData();
     formdata.append('action', 'sh_editvideo');
     formdata.append('uniqid', '<?php echo $uniqid ?>');
     formdata.append('sh_editvideo_nonce', '<?php echo wp_create_nonce( 'sh_editvideo' );?>');
     formdata.append('title', $('#title').val());
     formdata.append('video_description', $('#video_description').val());
     formdata.append('video_visible', $('input[name="video_visible"]:checked').val());
     formdata.append('video_private', $('input[name="video_private"]:checked').val());
     formdata.append('video_type', $('input[name="video_type"]:checked').val());
     formdata.append('video_vr_projection', $('#video_vr_projection').find(":selected").val());
     formdata.append('video_price', $('input[name="video_price"]').val());
     formdata.append('public_isdownload', $('input[name="video_isdownload_public"]').val());
     formdata.append('members_isdownload', $('input[name="video_isdownload_members"]').val());
     formdata.append('premium_isdownload', $('input[name="video_isdownload_premium"]').val());
     formdata.append('categories',  $("#catstable input:checkbox:checked").map(function(){ return $(this).val();}).get());
     var guestar = $('.guest_list p select[name="vguests[]"]').find(':selected').map(function() { if($(this).val() > 0) return $(this).val()}).get();
     formdata.append('guests', guestar.filter((item, index) => guestar.indexOf(item) === index));
     formdata.append('tags', $('input[name="video_tags[]"]').map(function(){ return $(this).val()}).get());
     formdata.append('post_type', 'sexhack_video');
<?php
   foreach(array('public','members','premium','preview','thumb','gif','gif_small') as $level) { ?>
      formdata.append('filename_<?php echo $level; ?>', $('input[name="filename_<?php echo $level; ?>"]').val());
   <?php } ?>

     $.ajax({url: '<?php echo admin_url( 'admin-ajax.php' );?>',
            type: 'POST',
            contentType: false,
            processData: false,
            data: formdata,
            success: function(response) {
               alert('saved');
     }
  });
});

   // (C1) NEW FLOW OBJECT
<?php
  foreach(array('public','members','premium','preview','thumb','gif','gif_small') as $level) { ?>
  var flow_<?php echo $level; ?> = new Flow({
    target: '<?php  echo admin_url( 'admin-ajax.php' ); ?>',
    chunkSize: 1024*1024, // 1MB
    uploadMethod:'POST',
    testChunks:false,
    query:{action:'file_upload', uniqid:'<?php echo $uniqid ?>', security:'<?php echo wp_create_nonce( 'sh_video_upload');?>', level:'<?php echo $level; ?>'},
    singleFile: true
  });

  var flowuploads=0;
  var needsupload=0;


  function canbesaved() {
     console.log(flowuploads+" - "+needsupload);
      if(flowuploads < 1 && needsupload > 0 && document.getElementById('title').value.length > 2) 
      {
         console.log("cansave");
         document.getElementById('send').disabled=false;
      } else {
         console.log("can't save");
         document.getElementById('send').disabled=true;
      }
  }

  document.getElementById('title').addEventListener('input', canbesaved);
  document.getElementById('title').addEventListener('propertychange', canbesaved);

  if (flow_<?php echo $level; ?>.support) {
    // (C2) ASSIGN BROWSE BUTTON
    flow_<?php echo $level; ?>.assignBrowse(document.getElementById("upBrowse_<?php echo $level; ?>"));
    // OR DEFINE DROP ZONE
    flow_<?php echo $level; ?>.assignDrop(document.getElementById("dropvideo_<?php echo $level; ?>"));
    
    // (C3) ON FILE ADDED
    flow_<?php echo $level; ?>.on("fileAdded", (file, evt) => {
      flow_<?php echo $level; ?>.cancel();
      document.getElementById("newvideo_<?php echo $level; ?>_list").innerHTML="";
      let fileslot = document.createElement("div");
      fileslot.id = file.uniqueIdentifier;
      fileslot.innerHTML = `${file.name} (${file.size}) - <strong>0%</strong>`;
      document.getElementById("newvideo_<?php echo $level; ?>_list").appendChild(fileslot);
    });

    // (C4) ON FILE SUBMITTED (ADDED TO UPLOAD QUEUE)
    flow_<?php echo $level; ?>.on("filesSubmitted", (arr, evt) => { 
      flowuploads++;
      flow_<?php echo $level; ?>.upload();
    });
 
    // (C5) ON UPLOAD PROGRESS
    flow_<?php echo $level; ?>.on("fileProgress", (file, chunk) => {
      let progress = (chunk.offset + 1) / file.chunks.length * 100;
      progress = progress.toFixed(2) + "%";
      let fileslot = document.getElementById(file.uniqueIdentifier);
      fileslot = fileslot.getElementsByTagName("strong")[0];
      fileslot.innerHTML = progress;
    });
 
    // (C6) ON UPLOAD SUCCESS
    flow_<?php echo $level; ?>.on("fileSuccess", (file, message, chunk) => {
      let fileslot = document.getElementById(file.uniqueIdentifier);
      fileslot = fileslot.getElementsByTagName("strong")[0];
      fileslot.innerHTML = "DONE";
      if(flowuploads) flowuploads--;
      console.log('CI SIAMO');
      ppid=fileslot.parentElement.parentElement.id;
      if(ppid=='newvideo_members_list' || ppid=='newvideo_public_list' || ppid=='newvideo_premium_list') needsupload++;
      $('#'+ppid).parent().find('input[type="hidden"]').val('<?php echo $uniqid."_"; ?>'+file.name);
      canbesaved();
    });
 
    // (C7) ON UPLOAD ERROR
    flow_<?php echo $level; ?>.on("fileError", (file, message) => {
      let fileslot = document.getElementById(file.uniqueIdentifier);
      fileslot = fileslot.getElementsByTagName("strong")[0];
      fileslot.innerHTML = "ERROR";
      if(flowuploads) flowuploads--;
      canbesaved();
    });


    // (C8) PAUSE/CONTINUE UPLOAD
    document.getElementById("upToggle_<?php echo $level; ?>").onclick = () => {
      if (flow_<?php echo $level; ?>.isUploading()) { flow_<?php echo $level; ?>.pause(); }
      else { flow_<?php echo $level; ?>.resume(); }
    };

    document.getElementById("delToggle_<?php echo $level; ?>").onclick = () => {
      if (flow_<?php echo $level; ?>.isUploading()) { 
           flow_<?php echo $level; ?>.cancel(); 
           if(flowuploads) flowuploads--;
           canbesaved();
           document.getElementById("newvideo_<?php echo $level; ?>_list").innerHTML="";
      }
    };
  }
<?php } ?>
});
</script>
