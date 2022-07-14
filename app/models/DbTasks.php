<?php

    namespace app\models;

    use JsonException;

    /**
     * Classe responsável pelas consultas na DB
     */
    class DbTasks
    {

        protected const JSON_FILE_NAME = __DIR__ . '/dbTasks.json';

        #region Getters
        /**
         * @return array|null
         *
         * @throws JsonException
         */
        public static function getTasksAvailable(): ?array
        {
            $dbTasks        = self::read();
            $tasksAvailable = [];

            if (!empty($dbTasks)) {
                foreach ($dbTasks as $dbTaskKey => $dbTask) {
                    if ($dbTask->status === true) {
                        $tasksAvailable[] = $dbTask;
                    }
                }
                return $tasksAvailable;
            }

            return null;
        }

        /**
         * @return array|null
         *
         * @throws JsonException
         */
        public static function getTasksDisabled(): ?array
        {
            $dbTasks       = self::read();
            $tasksDisabled = [];

            if (!empty($dbTasks)) {
                foreach ($dbTasks as $dbTaskKey => $dbTask) {
                    if ($dbTask->status === false) {
                        $tasksDisabled[] = $dbTask;
                    }
                }
                return $tasksDisabled;
            }

            return null;
        }

        /**
         * @return int
         * @throws JsonException
         */
        public static function countTasksDisabled()
        {
            return count(self::getTasksDisabled());
        }

        /**
         * @param int|array $taskId
         *
         * @return array|null
         * @throws JsonException
         */
        public static function getTasksById(int|array $taskId)
        {
            $tasks = self::getTasksAvailable();

            if (is_array($taskId)) {
                $tasksResult = [];

                foreach ($taskId as $id) {
                    foreach ($tasks as $task) {
                        if ($task->id === $id) {
                            $tasksResult[] = $task;
                        }
                    }
                }
                return $tasksResult ?? null;
            }

            if (is_int($taskId)) {
                foreach ($tasks as $task) {
                    if ($task->id === $taskId) {
                        return $task;
                    }
                }
                return null;
            }


            /**
            $task =  array_values(array_filter($tasks, static function($task) use ($taskId) {
                return $task->id === $taskId && $task->status === true;
                }));

            return reset($task);
             **/
        }
        #endregion

        #region Read&Save
        /**
         * @return mixed
         *
         * @throws JsonException
         */
        public static function read()
        {
            $json = file_get_contents(self::JSON_FILE_NAME);
            return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        }

        /**
         * @param array $data
         *
         * @return void
         *
         * @throws JsonException
         */
        public static function save(array $data)
        {
            file_put_contents(self::JSON_FILE_NAME, json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        }
        #endregion
    }