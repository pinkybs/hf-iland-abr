<?php
/*
 * Copyright 2008 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */ 

/**
 * osapiMediaItem - model class for mediaItems
 * @author Jesse Edwards 
 *
 */
class osapiMediaItem extends osapiModel {
  public $id;
  public $title;
  public $created;
  public $thumbnailUrl;
  public $description;
  public $duration;
  public $location;
  public $language;
  public $albumId;
  public $fileSize;
  public $startTime;
  public $rating;
  public $numVotes;
  public $numComments;
  public $numViews;
  public $tags;
  public $taggedPeople;
  public $mimeType;
  public $type;
  public $url;
  
  function setField($key, $value) {
    if(strtolower($key) == 'type') {
        $types = array('AUDIO' => 'audio', 'VIDEO' => 'video', 'IMAGE' => 'image');
        
      if (!array_key_exists(strtoupper($value), $types) && !in_array(strtolower($value), $types)) {  
        throw new Exception("Invalid Media type ($value)");   
      }else{
        $value = strtolower($value);
      }
    }
        
    parent::setField($key, $value);
  }
}
?>