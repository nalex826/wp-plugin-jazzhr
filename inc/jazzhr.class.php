<?php
/**
 * WP Custom Display JazzHR Class
 *
 * This class connects to the JazzHR API and retrieves job listings.
 */
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/* Check if Class Exists. */
if (! class_exists('JazzHr')) {
    class JazzHr
    {
        const JOB_URL = 'https://api.resumatorapi.com/v1/jobs/status/open/confidential/false/private/false';

        private $cached = '';
        private $api_url;

        /**
         * Connect to JazzHR API and fetch job listings.
         *
         * @return array|null array of job listings or null if API request fails
         */
        public function connect_jobs()
        {
            $jazzhr_api = get_option('jazzhr_api');
            if (! empty($jazzhr_api['apikey'])) {
                $this->api_url = static::JOB_URL . '/?apikey=' . $jazzhr_api['apikey'];
                $data          = $this->connect_curl();
                $decodedData   = json_decode($data, true);
                if (empty($decodedData['error']) && ! empty($decodedData)) {
                    return $this->capture_feed($decodedData);
                }
            }

            return null;
        }

        /**
         * Cache and return the job listings.
         *
         * @param array $decodedData decoded JSON data from API response
         *
         * @return array cached job listings
         */
        private function capture_feed($decodedData)
        {
            $output = $this->clean_feed($decodedData);
            $data   = json_encode($output, true);
            set_transient('jazzhr_cache', $data, 21600); // Cache for 6 hours

            return $output;
        }

        /**
         * Connect to JazzHR API using cURL.
         *
         * @return string API response data
         */
        private function connect_curl()
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->api_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $data = curl_exec($curl);
            curl_close($curl);

            return $data;
        }

        /**
         * Clean up job listings data.
         *
         * @param array $jobs raw job listings data
         *
         * @return array cleaned job listings data
         */
        private function clean_feed($jobs)
        {
            if (empty($jobs)) {
                return null;
            }
            $sortJobs = [];
            if (empty($jobs['id'])) {
                foreach ($jobs as $j) {
                    if (empty($j['id'])) {
                        continue;
                    }
                    $sortJobs[$j['id']] = $this->sanitize_job_data($j);
                }
                ksort($sortJobs);
            } else {
                $sortJobs[$jobs['id']] = $this->sanitize_job_data($jobs);
            }

            return $sortJobs;
        }

        /**
         * Sanitize job data.
         *
         * @param array $job raw job data
         *
         * @return array sanitized job data
         */
        private function sanitize_job_data($job)
        {
            return [
                'id'             => $job['id'],
                'title'          => $job['title'],
                'description'    => $this->filter_out($job['description']),
                'department'     => $job['department'],
                'city'           => $job['city'],
                'state'          => $job['state'],
                'country'        => $job['country'],
                'postalcode'     => $job['postalcode'],
                'type'           => $job['type'],
                'board_code'     => $job['board_code'],
                'experience'     => $job['experience'],
                'minimum_salary' => $job['minimum_salary'],
                'maximum_salary' => $job['maximum_salary'],
            ];
        }

        /**
         * Filter out unnecessary content from job description.
         *
         * @param string $content raw job description content
         *
         * @return string filtered job description content
         */
        private function filter_out($content)
        {
            $content = preg_replace('%class="[^"]+"%i', '', $content);
            $content = preg_replace('%style="[^"]+"%i', '', $content);
            $content = str_replace('<p>&nbsp;</p>', '', $content);
            $content = str_replace('<p >&nbsp;</p>', '', $content);
            $content = str_replace(['<h3>', '<h3 >', '</h3>'], ['<p>', '<p>', '</p>'], $content);
            $content = str_replace('For more information about the company, please visit www.petrolad.com', '', $content);

            return $content;
        }
    }
}
