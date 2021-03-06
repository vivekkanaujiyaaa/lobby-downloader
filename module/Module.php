<?php
namespace Lobby\Module;

use \Lobby\UI\Panel;

class app_downloader extends \Lobby\Module {

  public function init(){
    $this->app->downloadExists();

    if(isset($_POST["dName"]) && isset($_POST['newDInfo'])){
      $dName = $_POST["dName"];
      $newDInfo = json_decode($_POST["newDInfo"], true);

      if($this->app->downloadExists($dName)){
        $dInfo = $this->app->data->getArray($dName);

        if($dInfo["paused"] == "1"){
          echo "paused";
        }else{
          $this->app->data->saveArray($dName, $newDInfo);
        }
      }else{
        echo "cancelled";
      }
      $this->app->data->saveValue("lastDownloadStatusCheck", time());

      /**
       * We don't need to completely load Lobby.
       * So terminate
       */
      exit;
    }else if($this->app->isDownloadRunning()){
      $this->addDownloadNotifyItem();
    }else if($this->app->data->getValue("startOnLobbyOpen") === "1" && count($this->app->getActiveDownloads()) !== 0){
      /**
       * Start all active downloads
       */
      $moduleInit = 1;
      $this->app->inc("src/ar/init.php", array(
        "moduleInit" => true
      ));
    }else{
      /**
       * All downloads have been completed, so remove Notify item
       */
      $this->app->panel->removeNotifyItem("download_info");
    }
  }

  public function addDownloadNotifyItem(){
    $percentage = 1;
    foreach($this->app->getActiveDownloads() as $dInfo){
      $percentage *= $dInfo["percentage"] / 100;
    }

    /**
     * Get the combined percentage
     */
    $percentage *= 100;

    $this->app->panel->addNotifyItem("download_info", array(
      "contents" => "Downloading - ". round($percentage, 2) ."% <div class='progress'><div class='determinate' style='width: ". $percentage ."%;'></div></div>",
      "iconURL" => $this->app->srcURL . "/src/image/logo.svg"
    ));
  }

}
