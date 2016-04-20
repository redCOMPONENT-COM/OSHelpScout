<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

$tmpl = $this->isGuest ? 'guest' : 'registered';
?>
<div class="oshs-container oshs-container-<?php echo $tmpl; ?>">

    <h1><?php echo $this->title ?></h1>

    <?php echo $this->modulesContentTop; ?>

    <?php echo $this->loadTemplate($tmpl); ?>
</div>
