<?php

/**
 *
 */

namespace Drupal\subtonode\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\Core\Datetime;

class SubToNodeController extends ControllerBase {
  public function subtonode($sid) {
    //$sid = 2;
    $node_details = WebformSubmission::load($sid);
    $wf_changed = $node_details->getChangedTime();
    $submission_array = $node_details->getOriginalData();
    $title = $submission_array['title'];
    $body = $submission_array['body'];
    $contact_name = $submission_array['contact_name'];
    $contact_email = $submission_array['contact_email'];
    $contact_website_uri = $submission_array['website'];
    $contact_website_title = $submission_array['website'];
    $des_pub_date = $submission_array['bulletin_publish_date'];
    $image_fid = $submission_array['image'];


// Create file object from remote URL.
    if (!empty($image_fid)) {
      $file = \Drupal\file\Entity\File::load($image_fid);
      $path = $file->getFileUri();
      $data = file_get_contents($path);
      $node_img_file = file_save_data($data, 'public://' . $file->getFilename(), FILE_EXISTS_REPLACE);
    }

    $timestamp = date("Y-m-d\TH:i:s", strtotime($des_pub_date));

// Create node object with attached file.
    $node = Node::create([
      'type' => 'bulletin',
      'title' => $title,
      'body' => [
        'value' => $body,
        'summary' => '',
        'format' => 'markdown',
      ],
      'field_bulletin_audience' => '',
      'field_bulletin_contact_name' => $contact_name,
      'field_contact_name' => '',
      'field_bulletin_contact_email' => $contact_email,
      'field_contact_email' => '',
      'field_bulletin_desired_publicati' => $timestamp,
      'field_desired_publication_date' => '',
      'field_bulletin_reference_submiss' => [
        'target_id' => $sid,
      ],
      'field_bulletin_contact_website' => [
        'uri' => $contact_website_uri,
        'title' => $contact_website_title,
      ],
      'field_photo' => [
        'target_id' => (!empty($node_img_file) ? $node_img_file->id() : NULL),
        'alt' => 'Hello world',
        'title' => 'Goodbye world'
      ],
    ]);

    $target_ids_aud = $submission_array['audience'];
    foreach($target_ids_aud as $target_id){
      $node->field_bulletin_audience->AppendItem($target_id);
    }

    $target_ids_cat = $submission_array['category'];
    foreach($target_ids_cat as $target_id){
      $node->field_bulletin_category->AppendItem($target_id);
    }

    $node->save();

    return drupal_set_message(t('You have successfully created a node from webform submission @sid', array('@sid' => $sid)), 'success');;
  }
}

