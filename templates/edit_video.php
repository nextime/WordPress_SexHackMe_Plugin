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
namespace wp_SexHackMe;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
if ( !isset($video) || !is_object($video) ) exit;
$post = $video->get_post();
?>
<h3>Edit Video</h3>
<h4><?php echo $video->get_title(); ?></h4>
<form class="sexhack_video_edit" name="sexhack_video_edit" method="post">
   <input type="text" name="title" value="<?php echo $video->get_title(); ?>" />
   <input type=submit value="Save Video" />
</form>
<form class="fileUpload" enctype="multipart/form-data">
    <div class="form-group">
        <label>Choose File:</label>
        <input type="file" id="file" accept="video/*" />
    </div>
</form>
