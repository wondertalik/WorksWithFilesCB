<?php

/**
 * Класс предоставляет методы для копирования уже загруженных в КБ файлов
 * из одного или нескольких полей в одно любое другое.
 *
 * Class WorksWithFilesCB
 */
class WorksWithFilesCB
{

    protected $fieldsSource;
    protected $tableID;
    protected $lineID;

    const TYPE_FILE = 1; //Тип файла - файл
    const TYPE_IMG = 2; //Тип файла - изображение

    public function __construct($tableID)
    {
        $this->clear();
        $this->tableID = $tableID;

    }

    protected function getFilePath($fieldID, $lineID, $nameFile)
    {
        $file_path = get_file_path($fieldID, $lineID, $nameFile);
        return $file_path;
    }

    /**
     * Копирует все файлы из полей инициализированных через WorksWithFilesCB::addFieldOne  в другое поле
     * Стоит отметить, что файлы будут добавлятся к уже существующим.
     * Если необходимо полностью заменить на новые используется WorksWithFilesCB::replaceAllFiles
     *
     * @param int $destTableID айди таблицы в которую нужно скопировать
     * @param int $destFieldName айди поля в таблице в которое нужно скопировать
     * @param int $destLineID айди строки куда копировать
     * @throws Exception
     */
    protected function copy($destTableID, $destFieldName, $destLineID)
    {
        //Выбираем все файлы из полей
        $fields = $this->getFieldsSource();

        if (count($fields)) {
            //Получаем список уже существующих файлов в строке назначения
            $listFiles = $this->explodeFiles($destTableID, $destFieldName, $destLineID);
            $destFieldID = substr($destFieldName, 1);

            foreach ($fields as $value) {
                $nameField = $value['field'];
                $fieldID = substr($nameField, 1);

                //Получаем список файлов которые необходимо копировать
                $files = $this->explodeFiles($this->tableID, $nameField, $this->lineID);

                foreach ($files as $file) {
                    $destFieldID = substr($destFieldName, 1);
                    //Путь к файлу источнику
                    $file_path_old = $this->getFilePath($fieldID, $this->lineID, $file);
                    //Путь к файлу назначения
                    $file_path_new = $this->getFilePath($destFieldID, $destLineID, $file);
                    //Создаем необходимую структуру директорий
                    create_data_file_dirs($destFieldID, $destLineID, $file);
                    //Копируем файл
                    if (copy($file_path_old, $file_path_new)) {
                        //дополняем список
                        $listFiles[] = $file;
                    }
                }

            }

            $upd[$destFieldName] = implode("\r\n", $listFiles);
            data_update($destTableID, EVENTS_ENABLE, $upd, "`id` = ", $destLineID);
        } else {
            throw new Exception("Необходимо указать поле, из которого будет производится копирование");
        }

    }

    /**
     * Возвращает список файлов которые находятся в поле
     *
     * @param int $tableID
     * @param string $fieldName
     * @param int $lineID
     * @return array
     * @throws Exception
     */
    protected function explodeFiles($tableID, $fieldName, $lineID)
    {
        //Получаем список файлов в поле назначения
        $result = data_select_field($tableID, $fieldName, "`id` = ", $lineID, " LIMIT 1");
        if (sql_num_rows($result)) {
            $row = sql_fetch_assoc($result);

            //Парсим перечень файлов.
            if (strlen(trim($row[$fieldName])))
                return explode("\r\n", $row[$fieldName]);
            else
                return array();
        } else {
            throw new Exception("В поле " . $fieldName . " не удалось найти запись с ID " . $lineID . " в таблице " . $tableID);
        }
    }

    /**
     * @param $tableID
     * @param $fieldName
     * @param $lineID
     */
    protected function delFilesInFields($tableID, $fieldName, $lineID)
    {
        $fieldID = substr($fieldName, 1);
        $result = data_select_field($tableID, $fieldName, "`id` = ", $lineID);

        //Если была найдена строка
        if (sql_num_rows($result) > 0) {
            $row = sql_fetch_assoc($result);

            $files = explode("\r\n", $row[$fieldName]);
            foreach ($files as $file) {
                $file_path = $this->getFilePath($fieldID, $lineID, $file);
                @unlink($file_path);
            }
            data_update($tableID, array($fieldName => ''), "`id` = ", $lineID);
        }
    }

    public function clear()
    {
        $this->tableID = NULL;
        $this->fieldsSource = array();
        $this->lineID = NULL;
    }

    public function setLineID($lineID)
    {
        $this->lineID = intval($lineID);
    }

    public function getLineID()
    {
        return $this->lineID;
    }

    public function setTableID($tableID)
    {
        $this->tableID = $tableID;
    }

    public function getTableID()
    {
        return $this->tableID;
    }

    /**
     * Добавление поля, откуда будут копироватся файлы
     *
     * @param $field
     * @param int $type
     */
    public function addFieldOne($field, $type = WorksWithFilesCB::TYPE_IMG)
    {
        array_push($this->fieldsSource, array('field' => $field, 'type' => $type));
    }

    /**
     * Удаление поля, откуда будут копироватся файлы
     *
     * @param string $field имя поля, например f1292.
     */
    public function delFieldOne($field)
    {
        foreach ($this->fieldsSource as $key => $value) {
            if ($field == $value['field'])
                unset($this->fieldsSource[$key]);
        }
    }

    public function getFieldsSource()
    {
        return $this->fieldsSource;
    }

    /**
     * Копирует все файлы из полей инициализированных через WorksWithFilesCB::addFieldOne  в поле $destFieldName
     * Стоит отметить, что все файлы в поле назначении перед добавлением новых файлов удаляются.
     * Если необходимо полностью заменить на новые используется WorksWithFilesCB::replace
     *
     * @param int $destTableID айди таблицы в которую нужно скопировать
     * @param int $destFieldName айди поля в таблице в которое нужно скопировать
     * @param int $destLineID айди строки куда копировать
     * @throws Exception
     */
    public function replaceAllFiles($destTableID, $destFieldName, $destLineID)
    {
        $this->delFilesInFields($destTableID, $destFieldName, $destLineID);
        $this->copy($destTableID, $destFieldName, $destLineID);
    }


    /**
     * Копирует все файлы из полей инициализированных через WorksWithFilesCB::addFieldOne  в другое поле
     * Стоит отметить, что файлы будут добавлятся к уже существующим.
     * Если необходимо полностью заменить на новые используется WorksWithFilesCB::replaceAllFiles
     *
     * @param int $destTableID айди таблицы в которую нужно скопировать
     * @param int $destFieldName айди поля в таблице в которое нужно скопировать
     * @param int $destLineID айди строки куда копировать
     * @throws Exception
     */
    public function copyAllFiles($destTableID, $destFieldName, $destLineID)
    {
        $this->copy($destTableID, $destFieldName, $destLineID);
    }

}

