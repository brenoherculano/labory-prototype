<?php
    namespace app\views;

    use app\models\DbTasks;
    use app\controllers\TaskFunctions;
    use app\models\TaskGroups;

    TaskFunctions::init();
?>

<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html"
      xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <style>
        #task:hover {
            background-color: #3C3C3C;
        }
    </style>
    <title>Labory</title>
</head>
<body class="bg-secondary">
<div class="container my-5 rounded bg-dark border border-1 shadow" style="min-height: 500px; color:white">
    <div class="row">
        <div class="col m-4">
            <form action="/index.php" method="post">
                <!-- 24h mode switch -->
                <div class="form-check form-switch mb-3 disable">
                    <input class="form-check-input" type="checkbox" disabled>
                    <label class="form-check-label"><small>24h mode</small></label>
                </div>

                <!-- New Task insert -->
                <div class="row">

                    <div class="input-group">
                        <input class="col-5 form-control rounded-pill" type="text" name="newTaskName" placeholder=" New to-do here">

                        <select name="group" class="ms-2 custom-select rounded" id="inputGroupSelect04" aria-label="Exemplo de select com botão addon">

                            <option value="" selected disabled>Escolher grupo...</option>

                            <?php foreach (TaskGroups::getAllGroups() as $group) : ?>

                                <option value="<?= $group->groupName; ?>"><?= $group->groupName; ?></option>

                            <?php endforeach; ?>

                            <option value="" data-new-group="1">Novo grupo</option>


                        </select>
                        <div class="ms-1 input-group-append">
                            <input class="btn btn-primary" type="submit" name="newTaskBtn" value="Entry!">
                        </div>
                    </div>
                </div>
            </form>

            <!-- Modal to add new group with a task -->
            <div class="modal fade" id="addNewGroupModal"
                 data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel"
                 aria-hidden="true">
                <div class="modal-dialog ">
                    <div class="modal-content bg-dark">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewGroupModalHeaderLabel">Add a new task in a new group</h5>
                            <button type="button" class="btn-close bg-light" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <form action="/index.php" method="POST">
                                <div class="row ms-3 me-3 mb-3">
                                    <input class="col form-control rounded" type="text" name="newTaskName" placeholder=" New to-do name here" value="">
                                </div>
                                <div class="row ms-3 me-3 mb-3">
                                    <input class="col form-control rounded" type="text" name="newGroupName" placeholder=" Type here a name for the new group">
                                </div>
                                <div class="ms-3 me-3 mb-3">
                                    <input class="btn btn-primary" type="submit" name="createGroupAndTaskBtn" value="Create">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <h1 class="text-center m-4">To-do:</h1> <!-- This title should be editable -->

            <!-- Navs -->
            <ul class="nav nav-tabs">

                <div class="col nav">
                    <!-- Home button -->
                    <li class="nav-link p-0 align-self-end">
                        <a class="btn btn-primary font-italic" href="/">Home</a>
                    </li>

                    <!-- Groups navs -->
                    <?php foreach (TaskGroups::getAllGroups() as $group) : ?>

                        <li class="nav-link btn-group p-0 align-self-end <?php if (!empty($_GET['tabGroup']) && $_GET['tabGroup'] === $group->groupName) : ?>active bg-dark text-light <?php endif; ?>">

                            <!-- Group tab -->
                            <a class="btn btn-dark " href="?tabGroup=<?= $group->groupName ?>"><?= $group->groupName ?></a>

                            <!-- Dropdown button -->
                            <button class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>

                            <!-- Dropdwon menu -->
                            <ul class="dropdown-menu" id="groupDropdownMenu">
                                <li><a class="dropdown-item" id="renameThisGroup" href="javascript:void(0)">Change group's name</a></li>
                                <li><a class="dropdown-item" id="addTasksInThisGroup" href="javascript:void(0)">Add tasks to this group</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" id="deleteThisGroup" href="javascript:void(0)">Delete group</a></li>
                            </ul>
                        </li>

                        <!-- Modal to delete group-->
                        <div class="modal fade" id="deleteGroup<?= $group->groupName ?>"
                             data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabelDeleteGroup"
                             aria-hidden="true">
                            <div class="modal-dialog ">
                                <div class="modal-content bg-dark">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="staticBackdropLabelDeleteGroup">Delete "<?= $group->groupName ?>"?</h5>
                                        <button type="button" class="btn-close bg-light" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        After deleted, data cannot be recovery, are you sure?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Close
                                        </button>
                                        <a class="btn btn-danger" href="?deleteGroup=<?= $group->groupName ?>">Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                    <a class="btn btn-dark <?php if (!empty($_GET['tabGroup']) && $_GET['tabGroup'] === 'Done') : ?>active bg-dark text-light <?php endif; ?>" href="?tabGroup=Done">Done</a>

                </div>

                <!-- Add new group only button -->
                <li class="col-1 nav-item p-0 align-self-end">
                    <button class="nav-link p-2" data-bs-toggle="modal" data-bs-target="#addNewGroupOnlyModal">New group</button>
                </li>
            </ul>

            <!-- Modal to add new group only-->
            <div class="modal fade" id="addNewGroupOnlyModal"
                 data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel"
                 aria-hidden="true">
                <div class="modal-dialog ">
                    <div class="modal-content bg-dark">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addNewGroupModalHeaderLabel">Create a new group</h5>
                            <button type="button" class="btn-close bg-light" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <form action="/index.php" method="POST">
                                <div class="row ms-3 me-3 mb-3" hidden>
                                    <input class="col form-control rounded" type="text" name="newTaskName" placeholder=" New to-do name here" value="">
                                </div>
                                <div class="row ms-3 me-3 mb-3">
                                    <input class="col form-control rounded" type="text" name="newGroupName" placeholder=" Type here a name for the new group">
                                </div>
                                <div class="ms-3 me-3 mb-3">
                                    <input class="btn btn-primary" type="submit" name="createGroupAndTaskBtn" value="Create">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks: -->

            <?php
                $currentTab = $_GET['tabGroup'];

                if (!empty($currentTab)) {
                    if ($currentTab === 'Done') {
                        $tasks = DbTasks::getTasksDisabled();
                    } else {
                        $tasks = DbTasks::getTasksById(TaskGroups::getTasksIdsByGroup($_GET['tabGroup']));
                    }
                } else {
                    $tasks = DbTasks::getTasksAvailable();
                }
            ?>

            <?php if (!empty($tasks)): ?>

                <div class="row">
                    <div class="col"></div>
                    <div class="col-3 align-self-end text-muted m-2 rounded text-end" id="taskCunterDiv">
                        <small class="me-2 <?php if ($currentTab === 'Done') : ?> text-success <?php endif; ?>" id="taskCounter" >Hover to see number of the tasks</small>
                    </div>
                </div>

                <?php foreach ($tasks as $task) : ?>

                    <hr>
                    <div class="row" id="task">
                        <label class="col text-start"><?= $task->name ?></label>

                        <a class="col-lg-1 col-sm-2 btn btn-success btn-large" <?php if ($currentTab === 'Done') : ?> hidden <?php endif; ?> href="?doneBtn=<?= $task->id ?>&tabGroup=<?= $currentTab ?>">
                            Done
                        </a>
                        <div class="col-lg-2 col-sm-3 btn-group ">

                            <!-- Update Button trigger modal -->
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#updateStaticBackdrop<?= $task->id ?>">
                                Update
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="updateStaticBackdrop<?= $task->id ?>"
                                 data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel"
                                 aria-hidden="true">
                                <div class="modal-dialog ">
                                    <div class="modal-content bg-dark">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Update Field Name</h5>
                                            <button type="button" class="btn-close bg-light" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <form action="/index.php?" method="GET">
                                                <div>
                                                    <input type="hidden" name="taskId" value="<?= $task->id ?>">
                                                </div>
                                                <div class="row">
                                                    <input class="col me-2 form-control rounded-pill" type="text"
                                                           name="updateTaskName"
                                                           placeholder=" Type here a new name for the task"
                                                           value="<?= $task->name ?>">
                                                    <input class="col-2 btn btn-primary" type="submit"
                                                           name="updateBtn" value="Update">
                                                    <input type="text" name="tabGroup" value="<?= $currentTab ?>" hidden>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Button trigger modal -->
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStaticBackdrop<?= $task->id ?>">
                                Delete
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="deleteStaticBackdrop<?= $task->id ?>"
                                 data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel"
                                 aria-hidden="true">
                                <div class="modal-dialog ">
                                    <div class="modal-content bg-dark">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Delete task
                                                confirmation</h5>
                                            <button type="button" class="btn-close bg-light" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            After deleted, data cannot be recovery, are you sure?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Close
                                            </button>
                                            <a class="btn btn-danger" href="?deleteBtn=<?= $task->id ?>&tabGroup=<?= $currentTab ?>">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>

                <?php else: ?>

                    <div class="p-4">
                        <h4 class="text-muted text-center">No tasks available</h4>
                    </div>

                <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
<script>
    $(() => {
      const groups = $('#inputGroupSelect04');
      const myModal = new bootstrap.Modal(document.getElementById('addNewGroupModal'));

      groups.on('change', () => {
        const selected = $('option:selected', groups);
        const newGroup = +selected.data('new-group') === 1;

        if (newGroup) {
          myModal.show();
          groups.val('');
        }
      });
    });
</script>

<script>
    $(() => {
      $('a#deleteThisGroup').on('click', () => {

        const modal = new bootstrap.Modal(document.getElementById('deleteGroup<?= $group->groupName ?>'));
        modal.show();

        $('a#renameThisGroup').on('click', () => {
          alert('Function not available yet')
        })
      })

      $('a#addTasksInThisGroup').on('click', () => {
        alert('Function not available yet')
      })

      $('div#taskCunterDiv').hover(() => {
        $('small#taskCounter').text('<?= count($tasks) ?> task<?php if (count($tasks) > 1) : ?>s<?php endif; ?>');
      },() => {
        $('small#taskCounter').text('Hover to see number of tasks')
      });
    })
</script>

</body>
</html>