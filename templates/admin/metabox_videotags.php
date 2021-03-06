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
<p>
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
?>
<!--
<p class="hide-if-no-js"><button type="button" class="button-link tagcloud-link" id="link-video_tags" aria-expanded="false">Choose from the most used tags</button></p>
-->
