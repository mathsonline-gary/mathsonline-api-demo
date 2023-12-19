<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMenuItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu-item:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate SQL queries for menu items based on Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvFile = storage_path('app/seeds/menu_items.csv');
        $menuItems = $this->readCsv($csvFile);

        $sqlFile1 = storage_path('app/seeds/menu_items_regular.sql');
        $sqlFile2 = storage_path('app/seeds/menu_items_essential.sql');

        $this->generate($menuItems, $sqlFile1, $sqlFile2);
    }

    protected function readCsv($filename, $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = [];

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }

            fclose($handle);
        }

        return $data;
    }

    protected function generate($menuItems, $sqlFile1, $sqlFile2)
    {
        $sql1 = '';
        $sql2 = '';

        foreach ($menuItems as $menuItem) {
            if (str_contains($menuItem['CourseCaption'], 'Essential Maths')) {
                $sql2 .= $this->menuItemToSql($menuItem);
            } else {
                $sql1 .= $this->menuItemToSql($menuItem);
            }
        }

        file_put_contents($sqlFile1, $sql1);
        file_put_contents($sqlFile2, $sql2);

        $this->info('Generation done!');
    }

    protected function menuItemToSql(array $menuItem)
    {
        return sprintf("INSERT INTO menu_item (`MenuID`,`CourseID`,`StreamID`,`TopicID`,`LessonID`,`CourseCaption`,`StreamCaption`,`TopicCaption`,`LessonCaption`,`LessonCaptionHTML`,`Tutorial`,`TutorialMaster`,`DuplicateOfTutorial`,`Answers`,`AnswersFormat`,`Actions`,`Duration`,`SharedFlag`,`HasDiagnosticFlag`,`HasHTMLDiagnosticFlag`,`TutorialHash`,`NotForReporting`,`NationalOutcomeCode`,`StateOutcomeCode`) VALUES (%s,%s,%s,%s,%s,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%s,%s,'%s','%s','%s','%s','%s','%s','%s');\n",
            $menuItem['MenuID'], $menuItem['CourseID'], $menuItem['StreamID'], $menuItem['TopicID'], $menuItem['LessonID'], addslashes($menuItem['CourseCaption']), addslashes($menuItem['StreamCaption']), addslashes($menuItem['TopicCaption']), addslashes($menuItem['LessonCaption']), addslashes($menuItem['LessonCaptionHTML']), $menuItem['Tutorial'], $menuItem['TutorialMaster'], addslashes($menuItem['DuplicateOfTutorial']), addslashes($menuItem['Answers']), addslashes($menuItem['AnswersFormat']), $menuItem['Actions'], $menuItem['Duration'], addslashes($menuItem['SharedFlag']), addslashes($menuItem['HasDiagnosticFlag']), addslashes($menuItem['HasHTMLDiagnosticFlag']), addslashes($menuItem['TutorialHash']), addslashes($menuItem['NotForReporting']), addslashes($menuItem['NationalOutcomeCode']), addslashes($menuItem['StateOutcomeCode']));
    }
}
