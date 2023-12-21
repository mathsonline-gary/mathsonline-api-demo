<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMenuItems2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu-item:generate2';

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

        $sqlFile = storage_path('app/seeds/menu_items_result.sql');

        $this->generate($menuItems, $sqlFile);
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

    protected function generate($menuItems, $sqlFile)
    {
        $sql = '';

        foreach ($menuItems as $menuItem) {
            $sql .= $this->menuItemToSql($menuItem);
        }

        file_put_contents($sqlFile, $sql);

        $this->info('Generation done!');
    }

    protected function menuItemToSql(array $menuItem)
    {
        return sprintf("INSERT INTO menu_item (`MenuID`,`CourseID`,`StreamID`,`TopicID`,`LessonID`,`CourseCaption`,`StreamCaption`,`TopicCaption`,`LessonCaption`,`LessonCaptionHTML`,`Tutorial`,`TutorialMaster`,`DuplicateOfTutorial`,`Answers`,`AnswersFormat`,`Actions`,`Duration`,`SharedFlag`,`HasDiagnosticFlag`,`HasHTMLDiagnosticFlag`,`TutorialHash`,`NotForReporting`,`NationalOutcomeCode`,`StateOutcomeCode`) VALUES (%s,%s,%s,%s,%s,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',%s,%s,'%s','%s','%s','%s','%s','%s','%s');\n",
            $menuItem['MenuID'], $menuItem['CourseID'], $menuItem['StreamID'], $menuItem['TopicID'], $menuItem['LessonID'], addslashes($menuItem['CourseCaption']), addslashes($menuItem['StreamCaption']), addslashes($menuItem['TopicCaption']), addslashes($menuItem['LessonCaption']), addslashes($menuItem['LessonCaptionHTML']), $menuItem['Tutorial'], $menuItem['TutorialMaster'], addslashes($menuItem['DuplicateOfTutorial']), addslashes($menuItem['Answers']), addslashes($menuItem['AnswersFormat']), $menuItem['Actions'], $menuItem['Duration'], addslashes($menuItem['SharedFlag']), addslashes($menuItem['HasDiagnosticFlag']), addslashes($menuItem['HasHTMLDiagnosticFlag']), addslashes($menuItem['TutorialHash']), addslashes($menuItem['NotForReporting']), addslashes($menuItem['NationalOutcomeCode']), addslashes($menuItem['StateOutcomeCode']));
    }
}
