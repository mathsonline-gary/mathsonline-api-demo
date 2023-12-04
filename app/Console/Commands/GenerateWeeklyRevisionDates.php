<?php

namespace App\Console\Commands;

use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateWeeklyRevisionDates extends Command
{
    const YEAR = 2024;

    const TIMEZONE = 'Australia/Sydney';

    // Country IDs
    const AU = 1;
    const NZ = 3;

    // State IDs
    const ACT = 1;
    const NSW = 2;
    const NT = 3;
    const QLD = 4;
    const SA = 5;
    const TAS = 6;
    const VIC = 7;
    const WA = 8;
    const ALL_REGIONS = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weekly-revision-date:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly revision dates for different states and territories.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sqlFilePath = storage_path('app/seeds/weekly_revision_dates.sql');
        $sqlFileContent = '';
        $file = fopen($sqlFilePath, 'w');

        // Term dates for Australia states
        $auDates = [
            self::NSW => [
                1 => [
                    "from" => new Carbon('2024-01-30', self::TIMEZONE),
                    "to" => new Carbon('2024-04-12', self::TIMEZONE),
                ],
                2 => [
                    "from" => new Carbon('2024-04-29', self::TIMEZONE),
                    "to" => new Carbon('2024-07-05', self::TIMEZONE),
                ],
                3 => [
                    "from" => new Carbon('2024-07-22', self::TIMEZONE),
                    "to" => new Carbon('2024-09-27', self::TIMEZONE),
                ],
                4 => [
                    "from" => new Carbon('2024-10-14', self::TIMEZONE),
                    "to" => new Carbon('2024-12-20', self::TIMEZONE),
                ],
            ],

            self::ACT => [
                1 => [
                    "from" => new Carbon('2024-01-30', self::TIMEZONE),
                    "to" => new Carbon('2024-04-12', self::TIMEZONE),
                ],
                2 => [
                    "from" => new Carbon('2024-04-29', self::TIMEZONE),
                    "to" => new Carbon('2024-07-05', self::TIMEZONE),
                ],
                3 => [
                    "from" => new Carbon('2024-07-22', self::TIMEZONE),
                    "to" => new Carbon('2024-09-27', self::TIMEZONE),
                ],
                4 => [
                    "from" => new Carbon('2024-10-14', self::TIMEZONE),
                    "to" => new Carbon('2024-12-17', self::TIMEZONE),
                ],
            ],

            self::NT => [
                1 => [
                    "from" => new Carbon('2024-01-30', self::TIMEZONE),
                    "to" => new Carbon('2024-04-05', self::TIMEZONE),
                ],
                2 => [
                    "from" => new Carbon('2024-04-15', self::TIMEZONE),
                    "to" => new Carbon('2024-06-21', self::TIMEZONE),
                ],
                3 => [
                    "from" => new Carbon('2024-07-15', self::TIMEZONE),
                    "to" => new Carbon('2024-09-20', self::TIMEZONE),
                ],
                4 => [
                    "from" => new Carbon('2024-10-07', self::TIMEZONE),
                    "to" => new Carbon('2024-12-12', self::TIMEZONE),
                ],
            ],

            self::QLD => [
                1 => [
                    "from" => new Carbon('2024-01-22', self::TIMEZONE),
                    "to" => new Carbon('2024-03-28', self::TIMEZONE),
                ],
                2 => [
                    "from" => new Carbon('2024-04-15', self::TIMEZONE),
                    "to" => new Carbon('2024-06-21', self::TIMEZONE),
                ],
                3 => [
                    "from" => new Carbon('2024-07-08', self::TIMEZONE),
                    "to" => new Carbon('2024-09-13', self::TIMEZONE),
                ],
                4 => [
                    "from" => new Carbon('2024-09-30', self::TIMEZONE),
                    "to" => new Carbon('2024-12-13', self::TIMEZONE),
                ],
            ],

            self::SA => [
                1 => [
                    "from" => new Carbon('2024-01-29', self::TIMEZONE),
                    "to" => new Carbon('2024-04-12', self::TIMEZONE),
                ],
                2 => [
                    "from" => new Carbon('2024-04-29', self::TIMEZONE),
                    "to" => new Carbon('2024-07-05', self::TIMEZONE),
                ],
                3 => [
                    "from" => new Carbon('2024-07-22', self::TIMEZONE),
                    "to" => new Carbon('2024-09-27', self::TIMEZONE),
                ],
                4 => [
                    "from" => new Carbon('2024-10-14', self::TIMEZONE),
                    "to" => new Carbon('2024-12-13', self::TIMEZONE),
                ],
            ],

            self::TAS => [
                1 => [
                    "from" => new Carbon('2024-02-08', self::TIMEZONE),
                    "to" => new Carbon('2024-04-11', self::TIMEZONE),
                ],
                2 => [
                    "from" => new Carbon('2024-04-29', self::TIMEZONE),
                    "to" => new Carbon('2024-07-05', self::TIMEZONE),
                ],
                3 => [
                    "from" => new Carbon('2024-07-23', self::TIMEZONE),
                    "to" => new Carbon('2024-09-27', self::TIMEZONE),
                ],
                4 => [
                    "from" => new Carbon('2024-10-14', self::TIMEZONE),
                    "to" => new Carbon('2024-12-19', self::TIMEZONE),
                ],
            ],

            self::VIC => [
                1 => [
                    "from" => new Carbon('2024-01-30', self::TIMEZONE),
                    "to" => new Carbon('2024-03-28', self::TIMEZONE),
                ],
                2 => [
                    "from" => new Carbon('2024-04-15', self::TIMEZONE),
                    "to" => new Carbon('2024-06-28', self::TIMEZONE),
                ],
                3 => [
                    "from" => new Carbon('2024-07-15', self::TIMEZONE),
                    "to" => new Carbon('2024-09-20', self::TIMEZONE),
                ],
                4 => [
                    "from" => new Carbon('2024-10-07', self::TIMEZONE),
                    "to" => new Carbon('2024-12-20', self::TIMEZONE),
                ],
            ],

            self::WA => [
                1 => [
                    "from" => new Carbon('2024-01-31', self::TIMEZONE),
                    "to" => new Carbon('2024-03-28', self::TIMEZONE),
                ],
                2 => [
                    "from" => new Carbon('2024-04-15', self::TIMEZONE),
                    "to" => new Carbon('2024-06-28', self::TIMEZONE),
                ],
                3 => [
                    "from" => new Carbon('2024-07-15', self::TIMEZONE),
                    "to" => new Carbon('2024-09-20', self::TIMEZONE),
                ],
                4 => [
                    "from" => new Carbon('2024-10-07', self::TIMEZONE),
                    "to" => new Carbon('2024-12-12', self::TIMEZONE),
                ],
            ],
        ];

        // Term dates for New Zealand
        $nzDates = [
            self::ALL_REGIONS => [
                1 => [
                    "from" => new Carbon('2024-01-29', self::TIMEZONE),
                    "to" => new Carbon('2024-04-12', self::TIMEZONE),
                ],
                2 => [
                    "from" => new Carbon('2024-04-29', self::TIMEZONE),
                    "to" => new Carbon('2024-07-05', self::TIMEZONE),
                ],
                3 => [
                    "from" => new Carbon('2024-07-22', self::TIMEZONE),
                    "to" => new Carbon('2024-09-27', self::TIMEZONE),
                ],
                4 => [
                    "from" => new Carbon('2024-10-14', self::TIMEZONE),
                    "to" => new Carbon('2024-12-20', self::TIMEZONE),
                ],
            ]
        ];

        $countryDates = [
            self::AU => $auDates,
            self::NZ => $nzDates,
        ];

        foreach ($countryDates as $country => $termDates) {
            foreach ($termDates as $state => $dates) {
                // Calculate weekly revision dates for each term
                foreach ($dates as $term => $termDate) {
                    $weeklies = [];

                    // calculate the term period
                    $termPeriod = $termDate['to']->diffInWeeks($termDate['from']);

                    // Generate weekly revision dates for each term
                    switch ($term) {
                        case 1:
                        case 2:
                        case 3:
                            $weeklies = $termPeriod <= 8
                                ? $this->generateWeekliesFromTheStartDate($termDate['from'], $term)
                                : $this->generateWeekliesFromTheEndDate($termDate['to'], $term);

                            break;
                        case 4:
                            $weeklies = $this->generateWeekliesFromTheStartDate($termDate['from'], $term);

                            break;
                    }

                    ksort($weeklies);

                    // Save weekly revision dates to database
                    foreach ($weeklies as $week => $weekly) {
                        $this->info("Generating weekly revision dates for country {$country} - state {$state} - term {$term} - week {$week}");

                        /*WeeklyRevisionDate::updateOrCreate([
                            'year' => self::YEAR,
                            'state_id' => $state,
                            'term' => $term,
                            'week' => $week,
                            'starts_at' => $weekly['start'],
                            'ends_at' => $weekly['end'],
                        ]);*/

                        // Generate SQL seeder file for the old database
                        $sqlFileContent .= sprintf(
                            "INSERT INTO `weekly_revision_date` (`Year`, `CountryID`, `StateID`, `Week`, `StartDate`, `ExpiryDate`) VALUES (%d, %d, %d, %d, '%s', '%s') ON DUPLICATE KEY UPDATE `StartDate` = '%s', `ExpiryDate` = '%s';\n",
                            self::YEAR,
                            $country,
                            $state,
                            $week,
                            $weekly['start'],
                            $weekly['end'],
                            $weekly['start'],
                            $weekly['end'],
                        );
                    }
                }
            }

        }

        fwrite($file, $sqlFileContent);
        fclose($file);
    }

    /**
     * Generate 8 Monday-to-Monday weeks before the end of the term.
     *
     * @param Carbon $termDate
     * @param int    $term
     *
     * @return array
     */
    protected function generateWeekliesFromTheEndDate(Carbon $termDate, int $term): array
    {
        $weeklies = [];

        $endDate = (new Carbon($termDate, self::TIMEZONE))->addHours(8);
        if ($endDate->dayOfWeek !== CarbonInterface::MONDAY) {
            $endDate->previous('Monday');
        }

        $i = 0;

        while ($i < 8) {
            $weeklies[8 * $term - $i]["end"] = new Carbon($endDate);
            $weeklies[8 * $term - $i]["start"] = new Carbon($endDate->subDays(7));

            $i++;
        }

        return $weeklies;
    }

    /**
     * Generate 8 Monday-to-Monday weeks from the start of the term.
     *
     * @param Carbon $termDate
     * @param int    $term
     *
     * @return array
     */
    protected function generateWeekliesFromTheStartDate(Carbon $termDate, int $term): array
    {
        $weeklies = [];

        $startDate = (new Carbon($termDate, self::TIMEZONE))->addHours(8);
        if ($startDate->dayOfWeek !== CarbonInterface::MONDAY) {
            $startDate->next('Monday');
        }

        $i = 0;

        while ($i < 8) {
            $weeklies[$i + 8 * ($term - 1) + 1]["start"] = new Carbon($startDate);
            $weeklies[$i + 8 * ($term - 1) + 1]["end"] = new Carbon($startDate->addDays(7));

            $i++;
        }

        return $weeklies;
    }
}
