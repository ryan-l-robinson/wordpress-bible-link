<?php

/**
* Plugin Name: Bible Links
* Plugin URI: https://alliterationapplications.com
* Description: Adds links to bibles.org for text references detected
* Version: 0.0.1
* Author: Ryan Robinson
* Author URI: https://ryanrobinson.ca
* License: GPL2
 */


//TODO: reconsider this structure. Is it really better to have it save updated content with the links? That's annoying if it keeps adding a link that somebody tries to take out. Maybe there's a way to put an option per-post to disable it? Otherwise, forget updating the saved content and just update it on display.
add_action('save_post','bible_links_add_to_content');

//TODO: define the constant of all the valid Bible books with their abbreviations
//Needs to be a many-to-one map so that if any of the valid abbreviations appear, it knows to map it to the abbreviation used for Bibles.org
//First one in the array always needs to be the abbreviation used on bibles.org
function bible_links_define_valid_books() {
    return array(
        'GEN' => ['GEN','Gen','Genesis','Gn']
    );
}

function bible_links_define_translations() {
    return array(
        'NRSV' => '1fd99b0d5841e19b-02'
    );
}

function bible_links_add_to_content($post_id) {
    //read all the content of the post
    $content = get_the_content(null, false,$post_id);
    $valid_books = bible_links_define_valid_books();

    foreach($valid_books as $book_abbr) {
        $alternates = implode('|',$book_abbr); //implode all the possible abbreviations for easier regex use
        $regex = "/($alternates) (\d+):(\d+)/"; //TODO: update this to cover all the possible formats, but for now sticking to just making sure it finds the book name
        preg_match_all($regex,$content,$matches,PREG_SET_ORDER);
        foreach($matches as $match) {
            $book = $book_abbr[0]; //TODO: there should be a better way to get this without requiring the bibles.org version is the first in the array of options, since that is already the key
            $reference_text = $match[0];
            $start_chapter = $match[2];
            $start_verse = $match[3];
            $debugging .= "Rebuilt the reference for $reference_text using the components $book $start_chapter:$start_verse";

            $link = bible_links_generate_link($book,$start_chapter,$start_verse);

            //add link around reference content
            //TODO: make sure it doesn't add another link if it already within a link, or perhaps some other scenarios like in a header
            $content = str_replace($reference_text,'<a href="' . $link . '"/>' . $reference_text . '</a>',$content);
        }
    }

    //unhook this action so it doesn't loop indefinitely during the next save action
    remove_action('save_post','bible_links_add_to_content');

    //update the post content
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => "$content"
        )
    );

    //re-hook the function
    add_action('save_post','bible_links_add_to_content');
}

//Generates the properly formatted link based on the reference required
function bible_links_generate_link($book,$start_chapter,$start_verse,$end_chapter = null,$end_verse = null) {
    $translation_id = bible_links_define_translations()['NRSV']; //TODO in later versions: allow selection of translation
    return "https://bibles.org/bible/$translation_id/$book.$start_chapter?passageID=$book.$start_chapter.$start_verse-$end_chapter.$end_verse";
}

//TODO for next version: add an options page with choices such as translation version

?>