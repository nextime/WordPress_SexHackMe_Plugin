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

function sh_get_my_videos()
{
    // XXX Get Only the first result
   return SH_Query::get_Videos(get_current_user_id(), 'user');
}

function sh_save_video($video)
{
   if(is_object($video)) {
      // Initialize categories and tags is they are not.
      // Get from database active if not cached already.
      $video->get_categories(true);
      $video->get_tags(true);
      $video->get_guests(true);
      return SH_Query::save_Video($video);
   }
   return false;
}

function sh_delete_video($v)
{
   if(is_integer($v) && (intval($v)) > 0) return SH_Query::delete_Video($v);
   else if(is_object($v)) return SH_Query::delete_Video($v->id);
   return false;
}

function sh_delete_video_from_post($p)
{
   if(is_int($p) && $p > 0) return SH_Query::delete_Video($p, 'post');
   else if(is_object($p)) return SH_Query::delete_Video($p->ID, 'post');
   return false;
}

function sh_delete_video_from_product($p)
{
   if(is_int($p) and $p > 0) return SH_Query::delete_Video($p, 'product');
   else if(is_object($p)) return SH_Query::delete_Video($p->get_id(), 'product');
   return false;
}


function sh_get_videos_by_cat($vcat=false)
{
   return SH_Query::get_VideosCat($vcat);
}

function sh_get_video($id)
{
   return SH_Query::get_Video($id);
}

function sh_get_video_from_post($p)
{
   if(is_int($p) && $p > 0) return SH_Query::get_Video($p, 'post');
   else if(is_object($p)) return SH_Query::get_Video($p->ID, 'post');
   return false;
}

function sh_get_video_from_product($p)
{
   if(is_int($p) and $p > 0) return SH_Query::get_Video($p, 'product');
   else if(is_object($p)) return SH_Query::get_Video($p->get_id(), 'product');
   return false;
}

function sh_get_video_from_slug($slug)
{
   return SH_Query::get_VideoFromSlug($slug);
}

function sh_get_categories($id=false)
{
   return SH_Query::get_Categories($id);
}

function sh_get_video_categories($v)
{
   if(is_numeric($v) and $v > 0) return SH_Query::get_Video_Categories($v);
   else if(is_object($v)) return SH_Query::get_Video_Categories($v->id);
   return false;
}

function sh_get_video_guests($v)
{
   if(is_numeric($v) and $v > 0) return SH_Query::get_Video_Guests($v);
   else if(is_object($v)) return SH_Query::get_Video_Guests($v->ID);
   return false;
}


function sh_get_video_tags($v)
{
   if(is_numeric($v) and $v > 0) return SH_Query::get_Video_Tags($v);
   else if(is_object($v)) return SH_Query::get_Video_Tags($v->id);
   return false;
}

function sh_get_tag_by_name($name, $create=false)
{
   return SH_Query::get_Tag_By_Name($name, $create);
}

function sh_delete_tags_from_video($v)
{
   if(is_numeric($v) and $v > 0) return SH_Query::delete_Tags_assoc($v, 'video_id');
   else if(is_object($v)) return SH_Query::get_delete_Tags_assoc($v->id, 'video_id');
   return false;
}

function sh_delete_guests_from_video($v)
{  
   if(is_numeric($v) and $v > 0) return SH_Query::delete_Guests_assoc($v, 'video_id');
   else if(is_object($v)) return SH_Query::delete_Guests_assoc($v->id, 'video_id');
   return false;
}  


function sh_delete_categories_from_video($v)
{  
   if(is_numeric($v) and $v > 0) return SH_Query::delete_Categories_assoc($v, 'video_id');
   else if(is_object($v)) return SH_Query::delete_Categories_assoc($v->id, 'video_id');
   return false;
}


?>
