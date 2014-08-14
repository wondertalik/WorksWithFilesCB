<?php

namespace bitmaster\dp\ua\util {

    abstract class FilesCB
    {
        const TYPE_FILE = 1; //Тип файла - файл
        const TYPE_IMG = 2; //Тип файла - изображение
        protected $rootDir;

        public function __construct($path)
        {
            $this->setRootDir($path);
        }

        public final function setRootDir($path) {
            $this->rootDir = $path;
        }

        public final function getRootDir() {
            return $this->rootDir;
        }

        protected final function getFilePath($fieldID, $lineID, $nameFile)
        {
            $h=get_file_hash($fieldID,$lineID, $nameFile);
            $file_path = $this->getRootDir()."/files/".substr($h,0,2)."/".substr($h,2,2)."/".$h;
            return $file_path;
        }

        /**
         * Обновление списка файлов в поле
         *
         * @param $destTableID
         * @param $destFieldName
         * @param $destLineID
         * @param $listFiles
         */
        protected function updateField($destTableID, $destFieldName, $destLineID, $listFiles) {
            $upd[$destFieldName] = (string) implode("\r\n", $listFiles);
            data_update($destTableID, $upd, "`id` = ", $destLineID);
        }

    }

}