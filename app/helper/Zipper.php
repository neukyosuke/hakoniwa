<?php

declare(strict_types=1);

class Zipper extends \ZipArchive
{
    private $zip;
    private $create_flag = self::CREATE | self::EXCL | self::CHECKCONS;

    public function __construct($filename)
    {
        assert(extension_loaded("zip"));

        $this->zip = new \ZipArchive();
        $filename = "test.zip";
        $check = $this->zip->open($filename, $this->create_flag);
        if ($check !== true) {
            throw new \Exception($check);
        }
        $check = $this->zip->addEmptyDir(substr_replace(($filename), "", -4));
        if (!$check) {
            throw new \Exception("");
        }
    }

    public function addDirectory($dir): void
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException("{$dir} is not directory.");
        }
    }

    public function download(): void
    {
        header("Content-Type: application/force-download;");
        header("Content-Length: ".filesize($zip_tmp_path.$zip_name));
        header("Content-Disposition: attachment; filename=\"{$zip_name}\"");
        // readfile($zip_tmp_path.$zip_name);
    }
}
