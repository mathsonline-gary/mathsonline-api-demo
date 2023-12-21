<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateMenuItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oldMenuCsvFile = storage_path('app/seeds/menu_old.csv');
        if (!$oldMenu = $this->csvToObject($oldMenuCsvFile)) {
            $this->error('Failed to read old menu file');
            return;
        }
        $oldMenu = collect($oldMenu);

        $newMenuCsvFile = storage_path('app/seeds/menu_new.csv');
        if (!$newMenu = $this->csvToObject($newMenuCsvFile)) {
            $this->error('Failed to read new menu file');
            return;
        }
        $newMenu = collect($newMenu);

        $newMenu = $this->merge($oldMenu, $newMenu);

        dd($newMenu[53]);
    }

    protected function csvToObject($filename, $delimiter = ','): bool|array
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
                    $itemArr = array_combine($header, $row);
                    if (key_exists('MenuID', $itemArr)) {
                        $itemArr['MenuID'] = (int)$itemArr['MenuID'];
                    }
                    if (key_exists('CourseID', $itemArr)) {
                        $itemArr['CourseID'] = (int)$itemArr['CourseID'];
                    }
                    if (key_exists('StreamID', $itemArr)) {
                        $itemArr['StreamID'] = (int)$itemArr['StreamID'];
                    }
                    if (key_exists('TopicID', $itemArr)) {
                        $itemArr['TopicID'] = (int)$itemArr['TopicID'];
                    }
                    if (key_exists('LessonID', $itemArr)) {
                        $itemArr['LessonID'] = (int)$itemArr['LessonID'];
                    }
                    if (key_exists('Tutorial', $itemArr)) {
                        $itemArr['Tutorial'] = (int)$itemArr['Tutorial'];
                    }
                    if (key_exists('TutorialMaster', $itemArr)) {
                        $itemArr['TutorialMaster'] = (int)$itemArr['TutorialMaster'];
                    }
                    if (key_exists('Duration', $itemArr)) {
                        $itemArr['Duration'] = (int)$itemArr['Duration'];
                    }
                    if (key_exists('Actions', $itemArr)) {
                        $itemArr['Actions'] = (int)$itemArr['Actions'];
                    }

                    $data[] = (object)$itemArr;
                }
            }

            fclose($handle);
        }

        return $data;
    }

    protected function merge(Collection $oldMenu, Collection $newMenu): array
    {
        $mergedMenu = [];

        // Compute valid tutorial IDs
        $validTutorialIds = [];

        foreach ($newMenu as $newItem) {
            // Set Tutorial
            {
                // Only handle year K - 10A
                if ($newItem->CourseID <= 12) {
                    $oldItem = $oldMenu->first(function ($oldItem) use ($newItem) {
                        return $oldItem->MenuID === $newItem->MenuID
                            && $oldItem->CourseID === $newItem->CourseID
                            && $oldItem->Tutorial === $newItem->Tutorial
                            && $oldItem->TutorialMaster === $newItem->TutorialMaster;
                    });

                    if (!$oldItem) {
                        $newItem->Tutorial = 0;
                    }
                } else {
                    $newItem->Tutorial = 0;
                }
            }

            // Set DuplicateOfTutorial
            $newItem->DuplicateOfTutorial = "";

            // Set Answers & AnswersFormat
            {
                // TODO
                $newItem->Answers = "";
                $newItem->AnswersFormat = "";
            }

            // Set Actions
            {
                // TODO
                $newItem->Actions = "";
            }


            $newItem->Duration = 0;

            // Set SharedFlag
            $newItem->SharedFlag = $newItem->LessonID === 1 ? "Y" : "N";

            // Set HasDiagnosticFlag & HasHTMLDiagnosticFlag
            $newItem->HasDiagnosticFlag = "N";
            $newItem->HasHTMLDiagnosticFlag = "N";

            // Set TutorialHash
            {
                // TODO
                $newItem->TutorialHash = "";
            }

            // Set NotForReporting
            $newItem->NotForReporting = "N";

            // Set NationalOutcomeCode
            {
                // TODO
                $newItem->NationalOutcomeCode = "";
            }

            // Set StateOutcomeCode
            $newItem->StateOutcomeCode = "";

            $mergedMenu[] = $newItem;
        }

        return $mergedMenu;

    }
}
