<?php

    namespace app\controllers;

    use app\models\DbTasks;
    use app\models\TaskGroups;

    /**
     * Classe com CRUD e todas as funcionalidades das tasks
     */
    class TaskFunctions
    {
        private int $id;
        private string $name;
        private string $groupName;
        private array|null $dbTasks;
        private array|null $dbTasksDisabled; //Todo
        private string $message;

        private int $x = 1;

        public static function init()
        {
            $superGlobalVar = !empty($_POST) ? $_POST : $_GET;

            $tasks = new static();

            $tasks->dbTasks         = DbTasks::read();
            $tasks->dbTasksDisabled = DbTasks::getTasksDisabled();

            if (!empty((($superGlobalVar['newTaskBtn'] || $superGlobalVar['createGroupAndTaskBtn'])) && $superGlobalVar['newTaskName'])) {
                $tasks->name = (string)filter_var(trim($superGlobalVar['newTaskName']), FILTER_SANITIZE_STRING);

                if (array_key_exists('group',$superGlobalVar)) {
                    $tasks->groupName = (string)filter_var(trim($superGlobalVar['group']), FILTER_SANITIZE_STRING);
                } elseif (array_key_exists('newGroupName',$superGlobalVar)) {
                    $tasks->groupName = (string)filter_var(trim($superGlobalVar['newGroupName']), FILTER_SANITIZE_STRING);
                    TaskGroups::addNewGroup(filter_var($superGlobalVar['newGroupName'],FILTER_SANITIZE_STRING));
                } else {
                    $tasks->groupName = 'Nenhum';
                }

                $tasks->createTask();
            } elseif (empty($superGlobalVar['newTaskName']) && !empty($superGlobalVar['newGroupName']) && !empty($superGlobalVar['createGroupAndTaskBtn'])) {
                TaskGroups::addNewGroup(filter_var($superGlobalVar['newGroupName'],FILTER_SANITIZE_STRING));
            } elseif (!empty($superGlobalVar['doneBtn'])) {
                $tasks->id = (int)$superGlobalVar['doneBtn'];
                $tasks->markTaskAsDone();
            } elseif (!empty($superGlobalVar['updateBtn'] && $superGlobalVar['updateTaskName'] && $superGlobalVar['taskId'])) {
                $tasks->id   = (int)$superGlobalVar['taskId'];
                $tasks->name = (string)filter_var(trim($superGlobalVar['updateTaskName']), FILTER_SANITIZE_STRING);
                $tasks->updateTask();
            } elseif (!empty($superGlobalVar['deleteBtn'])) {
                $tasks->id = (int)$superGlobalVar['deleteBtn'];
                $tasks->deleteTask();
            } elseif (!empty($superGlobalVar['deleteGroup'])) {
                TaskGroups::deleteGroup($superGlobalVar['deleteGroup']);
            }
        }

        public function markTaskAsDone(): string
        {
            if (!empty($this->dbTasks)) {
                foreach ($this->dbTasks as $key => $dbTask) {
                    if ($dbTask->id === $this->id) {
                        $this->dbTasks[$key]->status = false;

                        DbTasks::save($this->dbTasks);
                        return 'Task marcked as done!';
                    }
                }
                unset($this->id);
            }
            return 'No data to be marked as done';
        }

        #region CRUD methods
        public function createTask(): string
        {

            $this->id = $this->x;

            if (!empty($this->dbTasks)) {
                foreach ($this->dbTasks as $key => $dbTask) {
                    if ($dbTask->id === $this->id) {
                        $this->x++;
                        return $this->createTask();
                    }

                    if (mb_convert_case($dbTask->name,MB_CASE_LOWER,'UTF-8') === mb_convert_case($this->name,MB_CASE_LOWER,'UTF-8')) {
                        return 'This Task already exists';
                    }
                }
            }

            $this->dbTasks[] = [
                'id'     => $this->id,
                'name'   => $this->name,
                'status' => true,
                'group'  => $this->groupName
            ];


            DbTasks::save($this->dbTasks);
            TaskGroups::addTaskInGroup($this->groupName,$this->id);
            unset($this->name);
            $this->x++;

            return 'New Task successfully recorded!';
        }

        public function updateTask(): string
        {
            if (!empty($this->dbTasks)) {
                foreach ($this->dbTasks as $dbTask) {
                    if ($dbTask->id === $this->id) {
                        $dbTask->name = $this->name;
                    }
                }
                DbTasks::save($this->dbTasks);
                return 'Task sucessfully updated';
            }
            return 'No data to be updated';
        }

        public function deleteTask(): string
        {
            if (!empty($this->dbTasks)) {
                foreach ($this->dbTasks as $key => $dbTask) {
                    if ($dbTask->id === $this->id) {
                        unset($this->dbTasks[$key]);
                        $this->dbTasks = array_values($this->dbTasks);
                        DbTasks::save(array_values($this->dbTasks));
                        TaskGroups::deleteInGroupTasks(TaskGroups::getGroupByTaskId($this->id),$this->id);
                        return 'Task successfully deleted';
                    }
                }
                return 'Task id not found on Db';
            }
            return 'No data to be deleted';
        }

        #endregion

    }