Для чего это нужно:

<ul>
	<li>копировать файлы и изображения из поля в указанное другое в пределах одной таблицы;</li>
	<li>копировать файлы и изображения из поля одной таблицы в указанное поле другой таблицы;</li>
	<li>копировать файлы и изображения из нескольких полей одновременно одной таблицы в любое другое указанное поле;</li>
</ul>
Копирование происходит в двух режимах:
<ul>
	<li>добавление файлов к уже существующим (если таковы имеются) в указанное поле;</li>
	<li>полное замещение в указанном поле файлами, которые требуется скопировать.</li>
</ul>

Как этим пользоватся:

<ol>
	<li>Создать через менеджер внешних файлов файл include/functions_custom.php, он не существует.</li>
	<li>Скопировать исходный класс в include/functions_custom.php</li>
</ol>

```php
//571 - айди таблицы, откуда копируем
$wfm = new WorksWithFilesCB(571);
//дальше указываем поля из которых нужно скопировать
$wfm->addFieldOne('f7251'); 
$wfm->addFieldOne('f7261');
$wfm->addFieldOne('f7271');
//указываем что копируем из строки с айди 71
$wfm->setLineID(71);

//Копируем файлы (добавление файлов к уже существующим)
$wfm->copyAllFiles(55, 'f7281', 1);
//полное замещение в указанном поле файлами, которые требуется скопировать
$wfm->replaceAllFiles(55, 'f7281', 1);