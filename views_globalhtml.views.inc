<?php

/**
 * @file
 * Contains views_globalhtml\views_globalhtml.views.inc..
 * Provide a custom views field data that isn't tied to any other module. */


/**
* Implements hook_views_data().
*/
function views_globalhtml_views_data() {

    $data['views']['table']['group'] = t('Custom Global');
    $data['views']['table']['join'] = [
      // #global is a special flag which allows a table to appear all the time.
      '#global' => [],
    ];

    $data['views']['ghtml_views_field'] = [
        'title' => t('Global Area Html Views'),
        'help' => t('Field Global Area Html Plugin Views'),
        'field' => [
            'id' => 'ghtml_views_field',
        ],
    ];
    return $data;
}
