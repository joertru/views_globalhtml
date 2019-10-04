<?php

namespace Drupal\views_globalhtml\Plugin\views\field;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("ghtml_views_field")
 */
class GhtmlViewsField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['hide_alter_empty'] = ['default' => TRUE];
    $options['field_html']  = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
   $form['field_html'] = [
      '#type' => 'textarea',
      '#title' => t('Custom Html'),
      '#default_value' => $this->options['field_html']
    ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $tokens = $this->getRenderTokens($this->options['field_html']);
    $twig_tokens = [];
    foreach ($tokens as $token => $replacement) {
      // Twig wants a token replacement array stripped of curly-brackets.
      // Some Views tokens come with curly-braces, others do not.
      // @todo: https://www.drupal.org/node/2544392
      if (strpos($token, '{{') !== FALSE) {
        // Twig wants a token replacement array stripped of curly-brackets.
        $token = trim(str_replace(['{{', '}}'], '', $token));
      }

      // Check for arrays in Twig tokens. Internally these are passed as
      // dot-delimited strings, but need to be turned into associative arrays
      // for parsing.
      if (strpos($token, '.') === FALSE) {
        // We need to validate tokens are valid Twig variables. Twig uses the
        // same variable naming rules as PHP.
        // @see http://php.net/manual/language.variables.basics.php
        assert(preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $token) === 1, 'Tokens need to be valid Twig variables.');
        $twig_tokens[$token] = $replacement;
      }
      else {
        $parts = explode('.', $token);
        $top = array_shift($parts);
        assert(preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $top) === 1, 'Tokens need to be valid Twig variables.');
        $token_array = [array_pop($parts) => $replacement];
        foreach (array_reverse($parts) as $key) {
          // The key could also be numeric (array index) so allow that.
          assert(is_numeric($key) || preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $key) === 1, 'Tokens need to be valid Twig variables.');
          $token_array = [$key => $token_array];
        }
        if (!isset($twig_tokens[$top])) {
          $twig_tokens[$top] = [];
        }
        $twig_tokens[$top] += $token_array;
      }
    }
    $build = [
      '#type' => 'inline_template',
      '#template' => $this->options['field_html'],
      '#context' => $twig_tokens,
     ];
     return $this->getRenderer()->render($build);
  }

}
