<?php

/**
 * messages actions.
 *
 * @package    OpenPNE
 * @subpackage messages
 * @author     Your name here
 */
class messagesActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }

  public function executeList(sfWebRequest $request)
  {

    return sfView::SUCCESS; 
  }
}
