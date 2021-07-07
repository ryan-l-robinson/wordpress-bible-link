<?php

add_action('save_post','bible_links_add_to_content');

//TODO: define the constant of all the valid Bible books with their abbreviations
//Needs to be a many-to-one map so that if any of the valid abbreviations appear, it knows to map it to the abbreviation used for Bibles.org
$valid_books = [
    'Gen' => ['Gen','Genesis','Gn'],
    'Ex' => ['Ex', 'Exodus', 'Exo']
];

$translations = [
    'NRSV' => '1fd99b0d5841e19b-02'
];

function bible_links_add_to_content($post_id) {
    //read all the content of the post
    $content = get_the_content(null, false,$post_id);

    //TODO: search all the content for the regex value
    foreach($valid_books as $book_abbr) {
        $alternates = explode($book_abbr,'|'); //explode all the possible abbreviations for easier regex use
        $regex = "$alternates \d+:\d+"; //TODO: this is just one common format for a single verse. I should test this far before I get carried away with the rest.
        preg_match_all($regex,$content,$matches);
        foreach($matches as $match) {
            //bible_links_generate_link();//TODO
        }
    }

    //TODO: call the function to generate the link and alter content to add the link around the reference
    //make sure it updates every repetition of the reference, once and only once (careful with the loops)

    //unhook this action so it doesn't loop indefinitely during the next save action
    remove_action('save_post','bible_links_add_to_content');

    //publish the post
    wp_update_post(array( 'ID' => $post_id, 'post_status' => get_post_status($post_id)));

    //re-hook the function
    add_action('save_post','bible_links_add_to_content');
}

//Generates the properly formatted link based on the reference required
function bible_links_generate_link($book,$start_chapter,$start_verse,$end_chapter = null,$end_verse = null) {
    return "https://bibles.org/bible/$translation_id/$book.$start_chapter?passageID=$book.$start_chapter.$start_verse-$end_chapter.$end_verse";
}

//TODO for next version: add an options page with choices such as translation version

?>