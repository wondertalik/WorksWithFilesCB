<?php

namespace bitmaster\dp\ua\util {

    require_once("FilesCB.php");

    class DelLostFilesCB extends FilesCB
    {
        protected $fields;

        public function __construct($path) {
            $this->clear();
            parent::__construct($path);
        }
        public function clear()
        {
            $this->fields = array();
        }

        protected function addField($tableID, $fieldName)
        {
            $this->fields[] = array('table' => $tableID, 'field' => $fieldName);
        }

        /**
         * Формирует список полей типа файл или изображение
         */
        public function getFieldList()
        {
            $list = array();

            $sqlQuery = "SELECT * FROM " . FIELDS_TABLE . " WHERE `type_field` IN (6, 9) AND `table_id` = 560";
            $result = sql_query($sqlQuery);
            if (sql_num_rows($result) > 0) {
                while ($row = sql_fetch_assoc($result)) {
                    $list[] = $row;
                }
                $this->fields = $list;
            }

            return $list;
        }

        public function cleanLostFileSystem()
        {

        }

        /**
         * Удаляем файлы которые есть в КБ, но нет в файловой системе
         */
        public function cleanLostDB()
        {
            foreach ($this->fields as $value) {
                //получаем список все строк в таблице
                $field = "f" . $value['id'];
                $result = data_select_field($value['table_id'], "`id`, " . $field, "`" . $field . "` != ''");
                if (sql_num_rows($result)) {
                    while ($row = sql_fetch_assoc($result)) {
                        $listFiles = $this->checkFilesFS($value['id'], $row['id'], $row[$field]);
                        $this->updateField($value['table_id'], $field, $row['id'], $listFiles);
                    }
                }
            }

            echo "Done\n";
        }

        /**
         * Проверяет наличие файла в каталоге файлов
         * Удаляет файл из списка если такого не существует
         *
         * @param int $fieldID айди поля
         * @param int $lineID айди строки в таблице
         * @param string $data строка списка файлов или изобрежаений в поле
         * @return array
         */
        protected function checkFilesFS($fieldID, $lineID, $data)
        {
            //Формируем список файлов
            $list = explode("\r\n", $data);
            foreach ($list as $key => $file_name) {
                //Полный путь к файлу
                $file_path = $this->rootDir . $this->getFilePath($fieldID, $lineID, $file_name);
                if (!file_exists($file_path)) {
                    unset($list[$key]);
                    $this->deleteDir($fieldID, $lineID, $file_name);
                }
            }
            return $list;
        }

        /**
         * Удаление директорий с файлами в случае если они пустые
         *
         * @param int $fieldID айди поля без f
         * @param int $lineID айди строки
         * @param string $fname имя файла
         */
        protected function deleteDir($fieldID, $lineID, $fname)
        {
            $h = get_file_hash($fieldID, $lineID, $fname);
            //путь к первому каталогу
            $d1 = $this->rootDir . "/files/" . substr($h, 0, 2);
            $d2 = $d1 . "/" . substr($h, 2, 2);

            @$dir_files = scandir($d2);
            if (count($dir_files) < 3) {
                @rmdir($d2);
                @$dir_files = scandir($d1);
                if (count($dir_files) < 3) {
                    @rmdir($d1);
                }
            }
        }
    }
}