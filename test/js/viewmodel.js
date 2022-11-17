(function(){
    'use strict';

    /**
     * test function
     * @param {string} desc
     * @param {function} fn
     */
    function it(desc, fn){
        try {
            fn();
            console.log('\x1b[32m%s\x1b[0m', '\u2714 ' + desc);
        }catch(error){
            console.log('\n');
            console.log('\x1b[31m%s\x1b[0m', '\u2718 ' + desc);
            console.error(error);
        }
    }

    function assert(isTrue){
        if(!isTrue){
            throw new Error();
        }
    }

    it('test project', function(){
        const project = new Project(1, 'テストProject', '#777777');
        assert(project.id === 1);
        assert(project.name() === 'テストProject');
        assert(project.color() === '#777777');
    });

    it('test viewmodel projects', function(){
        const viewModel = new ViewModel();
        assert(viewModel.column.projects.length === 0);

        const project = new Project(1, 'テストProject', '#777777');
        viewModel.column.projects.push(project);
        assert(viewModel.column.projects().length === 1);

        viewModel.column.projects.removeAll();
        assert(viewModel.column.projects().length === 0);
    });

    it('test task', function(){
        const project = new Project(2, 'テストProject', '#777777');
        const task = new Task(1, project, 'テストTask', '2021-09-22', 3);
        assert(task.id === 1);
        assert(task.project.id === 2);
        assert(task.project.name() === 'テストProject');
        assert(task.project.color() === '#777777');
        assert(task.value === 'テストTask');
        assert(task.completetionDate === '2021-09-22');
        assert(task.status === 3);
    });

    it('test viewmodel tasks', function(){
        const viewModel = new ViewModel();
        assert(viewModel.task.incompleteTasks().length === 0);
        assert(viewModel.task.completedTasks().length === 0);

        const project = new Project(2, 'テストProject', '#777777');
        const task = new Task(1, project, 'テストTask', '2021-09-22', 1);
        viewModel.task.incompleteTasks.push(task);
        assert(viewModel.task.incompleteTasks().length === 1);
        assert(viewModel.task.completedTasks().length === 0);

        task.status = 0;
        viewModel.task.incompleteTasks.remove(task);
        viewModel.task.completedTasks.push(task);
        assert(viewModel.task.incompleteTasks().length === 0);
        assert(viewModel.task.completedTasks().length === 1);

        viewModel.task.incompleteTasks.removeAll();
        viewModel.task.completedTasks.removeAll();
        assert(viewModel.task.incompleteTasks().length === 0);
        assert(viewModel.task.completedTasks().length === 0);
    });

    it('test task completed', function(){
        const project = new Project(2, 'テストProject', '#777777');
        const task = new Task(1, project, 'テストTask', '2021-09-22', 1);
        assert(task.isCompleted === false);

        task.status = 0;
        assert(task.isCompleted === true);
    });

    it('test task expired', function(){
        const today = new Date();
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);

        const project = new Project(2, 'テストProject', '#777777');
        const task = new Task(1, project, 'テストTask', `${today.getFullYear()}-${('0'+(today.getMonth()+1)).slice(-2)}-${('0'+today.getDate()).slice(-2)}`, 1);
        assert(task.isCompleted === false);
        assert(task.isExpired === false);

        task.completetionDate = `${yesterday.getFullYear()}-${('0'+(yesterday.getMonth()+1)).slice(-2)}-${('0'+yesterday.getDate()).slice(-2)}`;
        assert(task.isCompleted === false);
        assert(task.isExpired === true);

        task.status = 0;
        assert(task.isCompleted === true);
        assert(task.isExpired === false);
    });
})();
