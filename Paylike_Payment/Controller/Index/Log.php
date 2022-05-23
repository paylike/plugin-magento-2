<?php
namespace Lunar\Paylike\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

define("LOG_DIR", BP . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "log");
define("LOGS_DIR_NAME", LOG_DIR . DIRECTORY_SEPARATOR . "paylike");
define("LOGS_DATE_FORMAT", "Y-m-d-h-i-s");


class Log extends Action {
  public function __construct(JsonFactory $resultJsonFactory, Context $context) {
    $this->resultJsonFactory = $resultJsonFactory;
    parent::__construct($context);
  }

  public function execute() {
    $post = $this->getRequest()->getPostValue();

    if (isset($post["export"])) {
      return $this -> export();
    }

    if (isset($post["hasLogs"])) {
      return $this -> hasLogs();
    }

    if (isset($post["delete"])) {
      return $this -> deleteLogs();
    }

    if (isset($post["writable"])) {
      return $this -> writable();
    }

    return $this -> log();
  }

  private function writable() {
    $response = [
      "dir" => LOG_DIR,
      "writable" => is_writable(LOG_DIR),
    ];

    $result = $this->resultJsonFactory->create();
    return $result->setJsonData(json_encode($response));
  }

  private function deleteLogs() {
    $files = glob(LOGS_DIR_NAME . DIRECTORY_SEPARATOR . "*.log");
    foreach($files as $file) {
      unlink($file);
    }

    return null;
  }

  private function hasLogs() {
    $files = glob(LOGS_DIR_NAME . DIRECTORY_SEPARATOR . "*.log");
    $response = json_encode(array("hasLogs" => count($files) > 0));
    $result = $this->resultJsonFactory->create();
    return $result->setJsonData(count($files) > 0);
  }

  private function export() {
    $filename = LOGS_DIR_NAME . DIRECTORY_SEPARATOR . "export.zip";
    $zip = new \ZipArchive();
    $zip->open($filename, \ZipArchive::CREATE);

    $files = glob(LOGS_DIR_NAME . DIRECTORY_SEPARATOR . "*.log");
    foreach($files as $file) {
      $zip -> addFile($file, basename($file));
    }

    $zip -> close();

    $content = base64_encode(file_get_contents($filename));
    unlink($filename);

    $result = $this->resultJsonFactory->create();
    return $result->setJsonData($content);
  }

  private function log() {
    $post = $this->getRequest()->getPostValue();

    if (!is_dir(LOGS_DIR_NAME)) {
      mkdir(LOGS_DIR_NAME);
    }


    $date = date(LOGS_DATE_FORMAT, ($post["date"] / 1000));
    $id = $post["context"]["custom"]["quoteId"];
    $filename = LOGS_DIR_NAME . DIRECTORY_SEPARATOR . $date . "___" . $id . ".log";

    if (!file_exists($filename)) {
      $separator = "============================================================";
      file_put_contents($filename, $separator . PHP_EOL . json_encode($post) . PHP_EOL . $separator . PHP_EOL . PHP_EOL);
    }

    $newContent = PHP_EOL . date(LOGS_DATE_FORMAT) . ": " . $post["message"];
    file_put_contents($filename, $newContent, FILE_APPEND);

    return null;
  }
}
