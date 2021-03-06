<?php
/**
 * redirect handler
 *
 * @package page
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_REDIRECT_HANDLER');

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
switch ($_GET['action']) {
  case 'product':
    if (isset($_GET['products_id']) && zen_not_null($_GET['products_id'])) {
      $sql = "SELECT products_url from " . TABLE_PRODUCTS_DESCRIPTION . " WHERE products_id = :productId: AND language_id = :languageId:";
      $sql = $db->bindVars($sql, ':productId:', $_GET['products_id'], 'integer');
      $sql = $db->bindVars($sql, ':languageId:', $_SESSION['languages_id'], 'integer');
      $result = $db->execute($sql);
      if ($result->RecordCount()) {
        $zco_notifier->notify('NOTIFY_BEFORE_REDIRECT_ACTION_PRODUCT', array(), $_GET['products_id'], $_SESSION['languages_id']);
        zen_redirect(fixup_url($result->fields['products_url']));
      } else {
        $sql = "SELECT products_url from " . TABLE_PRODUCTS_DESCRIPTION . " WHERE products_id = :productId: AND language_id = :languageId:";
        $sql = $db->bindVars($sql, ':productId:', $_GET['products_id'], 'integer');
        $sql = $db->bindVars($sql, ':languageId:', DEFAULT_LANGUAGE, 'integer');
        $result = $db->execute($sql);
        if ($result->RecordCount()) {
          $zco_notifier->notify('NOTIFY_BEFORE_REDIRECT_ACTION_PRODUCT', array(), $_GET['products_id'], $_SESSION['languages_id']);
          zen_redirect(fixup_url($result->fields['products_url']));
        }
      }
    }
    break;
  case 'banner':
  $banner_query = "SELECT banners_url
                   FROM " . TABLE_BANNERS . "
                   WHERE banners_id = :bannersID";

  $banner_query = $db->bindVars($banner_query, ':bannersID', $_GET['goto'], 'integer');
  $banner = $db->Execute($banner_query);
  if ($banner->RecordCount() > 0) {
    zen_update_banner_click_count($_GET['goto']);
    zen_redirect($banner->fields['banners_url']);
  }
  break;
  case 'manufacturer':
  if (isset($_GET['manufacturers_id']) && zen_not_null($_GET['manufacturers_id'])) {
    $sql = "SELECT manufacturers_url
            FROM " . TABLE_MANUFACTURERS_INFO . "
            WHERE manufacturers_id = :manufacturersID
            AND languages_id = :languagesID";

    $sql = $db->bindVars($sql, ':manufacturersID', $_GET['manufacturers_id'], 'integer');
    $sql = $db->bindVars($sql, ':languagesID', $_SESSION['languages_id'], 'integer');
    $manufacturer = $db->Execute($sql);

    if ($manufacturer->RecordCount()) {
      // url exists in selected language

      if (zen_not_null($manufacturer->fields['manufacturers_url'])) {
        $sql = "UPDATE " . TABLE_MANUFACTURERS_INFO . "
                SET url_clicked = url_clicked+1, date_last_click = now()
                WHERE manufacturers_id = :manufacturersID
                AND languages_id = :languagesID";

        $sql = $db->bindVars($sql, ':manufacturersID', $_GET['manufacturers_id'], 'integer');
        $sql = $db->bindVars($sql, ':languagesID', $_SESSION['languages_id'], 'integer');
        $db->Execute($sql);
        zen_redirect($manufacturer->fields['manufacturers_url']);
      }
    } else {
      // no url exists for the selected language, lets use the default language then
      $sql = "SELECT mi.languages_id, mi.manufacturers_url
              FROM " . TABLE_MANUFACTURERS_INFO . " mi, " . TABLE_LANGUAGES . " l
              WHERE mi.manufacturers_id = :manufacturersID
              AND mi.languages_id = l.languages_id
              AND l.code = '" . DEFAULT_LANGUAGE . "'";

      $sql = $db->bindVars($sql, ':manufacturersID', $_GET['manufacturers_id'], 'integer');
      $manufacturer = $db->Execute($sql);

      if ($manufacturer->RecordCount() > 0) {

        if (zen_not_null($manufacturer->fields['manufacturers_url'])) {
          $sql = "UPDATE " . TABLE_MANUFACTURERS_INFO . "
                  SET url_clicked = url_clicked+1, date_last_click = now()
                  WHERE manufacturers_id = :manufacturersID
                  AND languages_id = :languagesID";

          $sql = $db->bindVars($sql, ':manufacturersID', $_GET['manufacturers_id'], 'integer');
          $sql = $db->bindVars($sql, ':languagesID', $_SESSION['languages_id'], 'integer');
          $db->Execute($sql);
          zen_redirect($manufacturer->fields['manufacturers_url']);
        }
      }
    }
  }
  break;
  default:
    $zco_notifier->notify('NOTIFY_REDIRECT_DEFAULT_ACTION');
}

// Should not normally get to this point, unless supplied redirect destination is invalid
$zco_notifier->notify('NOTIFY_HEADER_END_REDIRECT_HANDLER');
zen_redirect(zen_href_link(FILENAME_DEFAULT));
