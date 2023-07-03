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
?>
<script language="javascript">
jQuery(function($) {
    $('body').on('change', '#file', function() {
        $this = $(this);
        file_data = $(this).prop('files')[0];
        form_data = new FormData();
        form_data.append('file', file_data);
        form_data.append('action', 'file_upload');
        form_data.append('security', '<?php echo wp_create_nonce( 'sh_video_upload' );?>');
  
        $.ajax({
            url: '<?php echo admin_url( 'admin-ajax.php' );?>',
            type: 'POST',
            contentType: false,
            processData: false,
            data: form_data,
            success: function (response) {
                $this.val('');
                alert('File uploaded successfully.');
            }
        });
    });
});
</script>
<form class="fileUpload" enctype="multipart/form-data">
    <div class="form-group">
        <label>Choose File:</label>
        <input type="file" id="file" accept="image/*" />
    </div>
</form>
