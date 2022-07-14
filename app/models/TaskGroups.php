<?php

    namespace app\models;

    use app\controllers\TaskFunctions;
    use JsonException;

    /**
     * Classe responsável pelas consultas na DB de grupos
     */
    class TaskGroups
    {

        protected const JSON_FILE_NAME = __DIR__ . '/taskGroups.json';

        #region Getters
        /**
         * @return mixed
         * @throws JsonException
         */
        public static function getAllGroups()
        {
            return self::read();
        }

        /**
         * @param string $groupName
         *
         * @return string
         * @throws JsonException
         */
        public static function getOneGroup(string $groupName)
        {
            if (!empty($groupName)) {
                foreach (self::getAllGroups() as $group) {
                    if ($groupName === $group->groupName) {
                        return $group;
                    }
                }
                return 'Group not found on database';
            }
            return 'Missed groupname';
        }

        /**
         * @param int $taskId
         *
         * @return mixed|void
         * @throws JsonException
         */
        public static function getGroupByTaskId(int $taskId)
        {
            foreach (self::getAllGroups() as $group) {
                if (in_array($taskId, $group->tasksIds, true)) {
                    return $group->groupName;
                }
            }
        }

        /**
         * @param string $groupName
         *
         * @return null|array|string
         * @throws JsonException
         */
        public static function getTasksIdsByGroup(string $groupName = 'Nenhum')
        {
            $group = self::getOneGroup($groupName);

            return $group->tasksIds ?? null;
        }

        #endregion

        #region Create/Update/Delete methods
        /**
         * @param string $groupName
         *
         * @return false|int
         * @throws JsonException
         */
        public static function addNewGroup(string $groupName)
        {
            $groups = self::getAllGroups();

            foreach ($groups as $group) {
                if ($groupName === $group->groupName) {
                    return 'Group Already exists';
                }
            }

            $groups[] = [
                'groupName' => $groupName,
                'tasksIds' => []
            ];

            return self::save(array_values( $groups));
        }

        /**
         * @param string $groupName
         * @param int    $taskId
         *
         * @return false|int
         * @throws JsonException
         */
        public static function addTaskInGroup(string $groupName, int $taskId)
        {
            $groups = self::getAllGroups();

            if (!empty($groupName)) {
                foreach ($groups as $group) {
                    if ($groupName === $group->groupName) {
                        $group->tasksIds[] = (int)$taskId;
                        return self::save(  array_values($groups));
                    }
                }
                die('Group not found on database');
            }
            die('Missed group name');
        }

        /**
         * @param string $groupName
         * @param string $newGroupName
         *
         * @return false|int|string
         * @throws JsonException
         */
        public static function updateGroupName(string $groupName, string $newGroupName)
        {
            $groups = self::getAllGroups();

            if (!empty($groupName)) {
                foreach ($groups as $group) {
                    if ($groupName === $group->groupName) {
                        $group->groupName = $newGroupName;
                        return self::save(array_values($groups));
                    }
                }
                return 'Group not found on database';
            }
            return 'Missed group name';
        }

        /**
         * @param string $groupName
         *
         * @return false|int
         * @throws JsonException
         */
        public static function deleteGroup(string $groupName)
        {
            if (!empty($groupName)) {
                $groups = self::getAllGroups();
                $tasksIds = self::getTasksIdsByGroup($groupName);

                if (!empty($tasksIds)) {
                    foreach ($tasksIds as $taskId) {
                        self::changeTaskGroup($taskId,'Nenhum');
                    }
                }

                if (!empty($groups)) {
                    foreach ($groups as $groupK => $groupV) {
                        if ($groupName === $groupV->groupName) {
                            unset($groups[$groupK]);
                            return self::save(array_values($groups));
                        }
                    }
                    die('Group not found on database');
                }
            }
            die('Missed group name');
        }

        /**
         * @param string $groupName
         * @param int    $taskId
         *
         * @return false|int|void
         * @throws JsonException
         */
        public static function deleteInGroupTasks(string $groupName, int $taskId)
        {
            $groups = self::getAllGroups();

            if (!empty($groupName)) {
                foreach ($groups as $group) {
                    if ($groupName === $group->groupName) {
                        foreach ($group->tasksIds as $key => $inGroupTaskId) {
                            if ($inGroupTaskId === $taskId) {
                                unset($group->tasksIds[$key]);
                                $group->tasksIds = array_values($group->tasksIds);
                                return self::save( array_values($groups));
                            }
                        }
                    }
                }
                die('Group not found on database');
            }
            die('Missed group name');
        }


        /**
         * @param string $groupName
         *
         * @return bool
         * @throws JsonException
         */
        public static function groupExists(string $groupName)
        {
            if (!empty($groupName)) {
                foreach (self::getAllGroups() as $group) {
                    if ($group->groupName === $groupName) {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * @param int    $taskId
         * @param string $newGroupName
         *
         * @return string
         * @throws JsonException
         */
        public static function changeTaskGroup(int $taskId, string $newGroupName)
        {
            if (!empty($taskId) && self::groupExists($newGroupName)) {
                $dbTasks = DbTasks::getTasksAvailable();

                foreach ($dbTasks as $dbTask) {
                    if ($dbTask->id === $taskId) {

                        self::addTaskInGroup($newGroupName,$dbTask->id);
                        self::deleteInGroupTasks($dbTask->group,$taskId);

                        $dbTask->group = $newGroupName;
                        DbTasks::save(array_values($dbTasks));

                        return 'Sucess';
                    }
                }
            }
            die('changeTaskGroup Failed');
        }

        #endregion

        #region Read&Save
        /**
         * @return mixed
         * @throws JsonException
         */
        public static function read()
        {
            $json = file_get_contents(self::JSON_FILE_NAME);
            return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        }

        /**
         * @param array $groups
         *
         * @return false|int
         * @throws JsonException
         */
        public static function save(array $groups)
        {
            return file_put_contents(self::JSON_FILE_NAME, json_encode($groups, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT), LOCK_EX);
        }
        #endregion
    }