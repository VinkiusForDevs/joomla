<?php
namespace Joomla\Component\Vinkius\Administrator\View\Vinkius;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView {
  public function display($tpl = null) {
    $this->document->addScript('/administrator/components/com_vinkius/admin/js/vinkius-connector.js');
    parent::display($tpl);
  }
}