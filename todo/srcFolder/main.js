/**
 * //サウンドの変更
 * @function changeSound
 * //表示関係
 * //選択したプロジェクトのタスクを取得し、画面更新
 * @function displayTaskOfSelectProject
 * //プロジェクトのタスク一覧を取得して表示する
 * @function displayTasks
 * //日付を指定してタスクを取得
 * @function displayTasksByDayColumn
 * 
 * //タスクのボタン有効化
 * //指定されたタスク削除ボタンを有効化、削除後は表示を更新する
 * @function enableTaskDeleteButton
 * //プロジェクト選択時のタスク完了状態チェックボタン有効化
 * @function enableTaskStatusChangeButton
 * 
 * //プロジェクト関係
 * //プロジェクトの一覧を取得して表示する
 * @function displayProjects
 * //プロジェクトの一覧初期表示
 * @function initProjects
 * //プロジェクト入力欄選択中に、Enterを押した際
 * @function inputProject
 * //プロジェクトの削除
 * @function deleteProject
 * 
 * //入力フォーム
 * //タスク入力欄選択中にEnter
 * @function inputTask
 * //日付入力欄に初期値（今日）を設定
 * @function initTaskDate
 * 
 * //左上4ボタン
 * //今日ボタン
 * @function clickToday
 * //明日ボタン
 * @function clickTomorrow
 * //それ以降ボタン
 * @function clickLater
 * //完了済みボタン
 * @function clickCompleted
 * //未完了ボタン
 * @function clickIncomplete
 * 
 * //表示、非表示処理
 * //タスク入力フォームを非表示にする
 * @function removeHiddenClassOfTaskForm
 * //完了済みタスクの表示非表示 
 * @function openComplete
 */

//ビューモデルクラス
class ViewModel {
    #projects = ko.observableArray();
    #incompleteTasks = ko.observableArray();
    #completedTasks = ko.observableArray();
    //完了済みタスクの表示フラグ(0:非表示)
    #openCompleteTaskFlag = ko.observable(0);
    constructor() {
    }

    //プロジェクト一覧
    get projects(){ return this.#projects; }
    selectProject = function(project){
        //タスクを追加するフォームを表示する
        if($(".main-todo-body-tasks-task").hasClass("hidden")){
            $(".main-todo-body-tasks-task").removeClass("hidden");
        }
        //タスク一覧のタイトル設定
        $("#todo_title").text(project.name);
        //プロジェクト一覧アイテム選択
        $(".selected_column").removeClass("selected_column");
        $(`[data-project_id=${project.id}]`).addClass("selected_column");
        //プロジェクトのタスク一覧表示
        displayTaskOfSelectProject(project);
        return true;
    }

    //タスク一覧
    get incompleteTasks(){ return this.#incompleteTasks; }
    get completedTasks(){ return this.#completedTasks; }

    //完了済みタスクの表示フラグ
    get openCompleteTaskFlag() { return this.#openCompleteTaskFlag; }
    set openCompleteTaskFlag(value) { this.#openCompleteTaskFlag(value); }
}

//プロジェクトクラス(モデル)
class Project {
    #id = -1;
    #name = '';
    #color = ko.observable('#777777');
    constructor(id, name, color){
        this.id = id;
        this.name = name;
        this.color = color;
    }

    //プロジェクトID
    get id(){ return this.#id; }
    set id(value){ this.#id = value; }

    //名称
    get name(){ return this.#name; }
    set name(value){ this.#name = value; }

    // 色
    get color(){ return this.#color; }
    set color(value){ this.#color(value); }
}

//タスククラス(モデル)
class Task{
    #id = -1;
    #project = null;
    #value = '';
    #completetionDate = '9999-12-31';
    #status = 1;
    constructor(id, project, value, completetionDate, status){
        this.id = id;
        this.project = project;
        this.value = value;
        this.completetionDate = completetionDate;
        this.status = status;
    }

    //タスクID
    get id(){ return this.#id; }
    set id(value){ this.#id = value; }

    //プロジェクトID
    get project(){ return this.#project; }
    set project(value){ this.#project = value; }

    //内容
    get value(){ return this.#value; }
    set value(value){ this.#value = value; }

    //期限
    get completetionDate(){ return this.#completetionDate; }
    set completetionDate(value){ this.#completetionDate = value; }

    //ステータス
    get status(){ return this.#status; }
    set status(value){ this.#status = value; }

    //完了したか？
    get isCompleted(){ return this.#status != 1; }

    //期限切れか？
    get isExpired(){
        if (this.isCompleted) return false;
        const completetion_array = this.completetionDate.split("-");
        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth() + 1;
        const date = today.getDate();
        if(year < completetion_array[0]) return false;
        if(year == completetion_array[0] && month < completetion_array[1]) return false;
        if(year == completetion_array[0] && month == completetion_array[1] && date <= completetion_array[2]) return false;
        return true;
    }
}

//ビューモデル
const vm = new ViewModel();
ko.applyBindings(vm);

//ダークモードの設定
const options = {
    bottom: '32px', // default: '32px'
    right: '32px', // default: '32px'
    left: 'unset', // default: 'unset'
    time: '0.5s', // default: '0.3s'
    mixColor: '#fff', // default: '#fff'
    backgroundColor: '#fff',  // default: '#fff'
    buttonColorDark: '#100f2c',  // default: '#100f2c'
    buttonColorLight: '#fff', // default: '#fff'
    saveInCookies: true, // default: true,
    label: '🌓', // default: ''
    autoMatchOsTheme: true // default: true
}
//豪華な音
const luxurySoundSrc = "./sound/great.mp3";
const luxurySoundIcon = "campaign";
//シンプルな音
const simpleSoundSrc = "./sound/simple.mp3";
const simpleSoundIcon = "notifications";
//無音
const notSoundSrc = "";
const notSoundIcon = "notifications_off";
//サウンドの初期設定
let sound = new Audio();
const soundVolume = 0.3;
let soundNum = 0;

window.onload = function(){
    //cookieの読み込み
    const readCookies = document.cookie;
    const readCookiesArray = readCookies.split(";");
    let soundCookie = 0;
    readCookiesArray.forEach(element => {
        const cookie = element.split("=");
        if(cookie[0] === "todoSoundType"){
            soundCookie = cookie[1];
        }
    });
    //サウンドの初期設定
    soundNum = Number(soundCookie);
    sound.volume = soundVolume;
    //cookieに合わせたサウンドの切り替え
    changeSound();

    //ダークモード機能
    const darkmode = new Darkmode(options);
    darkmode.showWidget();
    //左上リストのボタン機能
    document.getElementById("today_task"          ).addEventListener("click", clickToday    );
    document.getElementById("tomorrow_task"       ).addEventListener("click", clickTomorrow );
    document.getElementById("later_task"          ).addEventListener("click", clickLater    );
    document.getElementById("completed_task"      ).addEventListener("click", clickCompleted);
    document.getElementById("incomplete_task"     ).addEventListener("click", clickIncomplete);

    //サウンドボタン有効化
    document.getElementById("sound").addEventListener("click", function(){
        //サウンドの切り替え
        soundNum = (soundNum + 1) % 3;
        changeSound();
    });

    //プロジェクトの一覧初期表示
    initProjects();
    //日付入力欄に初期値（今日）を入力
    initTaskDate();
    //Enterキー入力時の機能
    document.body.addEventListener("keydown", event =>{
        if(event.key === "Enter"){
            let current = document.activeElement;
            //プロジェクト入力欄選択中にEnter
            if(current.id === "input_project"){
                let text = current.value;
                //値が入力されているときのみ処理を行う
                if(text !== ""){
                    inputProject(text);
                }
            //タスク入力欄選択中にEnter
            }else if(current.id === "input_task" || current.id === "input_date"){
                let text = $("#input_task").val();
                if(text !== ""){
                    inputTask(text);
                }
            }
        }
    });
}

/**
 * サウンド設定切り替え
 */
function changeSound(){
    switch(soundNum){
        case 0:
            sound.src = notSoundSrc;
            document.getElementById("sound").innerText = notSoundIcon;
            break;
        case 1:
            sound.src = simpleSoundSrc;
            document.getElementById("sound").innerText = simpleSoundIcon;
            break;
        case 2:
            sound.src = luxurySoundSrc;
            document.getElementById("sound").innerText = luxurySoundIcon;
            break;
    }
    //cookieの保存
    document.cookie = `todoSoundType=${soundNum}; expires=Fri, 31 Dec 9999 23:59:59 GMT`;
}

/**
 * @function 選択したプロジェクトのタスクを取得し、画面更新
 * @param project_id:選択したプロジェクトのid
 */
function displayTaskOfSelectProject(project){

    //タスクを追加するフォームを表示する
    if($(".main-todo-body-tasks-task").hasClass("hidden")){
        $(".main-todo-body-tasks-task").removeClass("hidden");
    }
    //タスク画面で、選択中のプロジェクトタイトルを変更
    let selectedProjectName = $(".selected_column p").text();
    $("#todo_title").text(selectedProjectName);
    //タスク一覧を表示
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: project.id,
                todo: "selectTasks"
            },
            dataType: "json"
        }
    ).done(function(tasksDataArray){
        //返ってきたタスク処理する
        displayTasks(tasksDataArray);
    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクを表示できません\n" + e);
    });
}

/**
 * プロジェクトのタスク一覧を取得して表示する
 * @param tasksDataArray:
 */
function displayTasks(tasksDataArray){
    //変更前のタスク一覧を削除する
    vm.incompleteTasks.removeAll();
    vm.completedTasks.removeAll();
    for(let i=0; i<tasksDataArray.length; i++){
        let task_id = tasksDataArray[i]["task_id"];
        //新しいタスクのプロジェクトID
        let project_id = tasksDataArray[i]["project_id"];
        //新しいタスクの内容
        let task_value = tasksDataArray[i]["task_value"];
        //新しいタスクの期限
        let task_completetion_date = tasksDataArray[i]["completetion_date"];
        //新しいタスクのstatus
        let task_status = tasksDataArray[i]["task_status"];
        let project = null;
        vm.projects().forEach(function(v, i){
            if(v.id != project_id) return;
            project = v;
        });

        const task = new Task(task_id, project, task_value, task_completetion_date, task_status);
        if(task.isCompleted){
            vm.completedTasks.push(task);
        }else{
            vm.incompleteTasks.push(task);
        }
    }
    const showCheck = !$(".selected_column").hasClass("main-column-completed");
    if(!showCheck){
        $(".check_task").remove();
    }
}

/**
 * 日付を指定してタスクを取得
 * @param day :検索表示する日付 
 * 
*/
 function displayTasksByDayColumn(day){
    const todo = "selectTasks";
    let dateFrom = null;
    let dateTo = null;
    let status = null;
    switch(day){
        case "today":
            dateFrom = new Date();
            dateTo = dateFrom;
            break;
        case "tomorrow":
            dateFromTimestamp = new Date().getTime() + (1000 * 60 * 60 * 24 * 1);
            dateFrom = new Date(dateFromTimestamp);
            dateTo = dateFrom;
            break;
        case "later":
            dateFromTimestamp = new Date().getTime() + (1000 * 60 * 60 * 24 * 2);
            dateFrom = new Date(dateFromTimestamp);
            break;
        case "incomplete":
            dateToTimestamp = new Date().getTime() - (1000 * 60 * 60 * 24 * 1);
            dateTo = new Date(dateToTimestamp);
            break;
        case "completed":
            status = "0";
            break;
    }
    let dateFromStr = null;
    if(dateFrom != null){
        dateFromStr = dateFrom.getFullYear() + "-" + (dateFrom.getMonth() + 1) + "-" + dateFrom.getDate();
    }
    let dateToStr = null;
    if(dateTo != null){
        dateToStr = dateTo.getFullYear() + "-" + (dateTo.getMonth() + 1) + "-" + dateTo.getDate();
    }

    //Ajax
    $.ajax("./post.php",
            {
                type: "POST",
                data:{
                    date_from: dateFromStr,
                    date_to: dateToStr,
                    status: status,
                    todo: todo
                },
                dataType: "json"
            }
        ).done(function(tasksDataArray){
            displayTasks(tasksDataArray);
        }).fail(function(XMLHttpRequest, status, e){
            alert("タスクを表示できませんでした\n" + e);
        });
}

/**
 * @function 指定のタスクの削除ボタンを有効化する
 * ボタンをクリックした際、プロジェクト選択時はそのid（異なる場合はNull）を送る
 * データベースからタスクの情報を削除し、表示を更新する
 * @param task_id_data:dataオブジェクト
 */
 function enableTaskDeleteButton(task){
    //プロジェクト選択はプロジェクトのidをAjaxで送る
    //
    let project_id;
    if($(".selected_column").hasClass("main-column-projects-project")){
        project_id = task.project.id;
    }else{
        project_id = null;
    }
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: project_id,
                task_id: task.id,
                todo: "deleteTask"
            },
            dataType: "json"
        }
    ).done(function(tasksDataArray){
        //返ってきたタスク処理する
        //プロジェクト選択時ではない場合、tasksDataArrayには"notProject"が入っている
        if(tasksDataArray !== "notProject"){
            vm.incompleteTasks.remove(task);
            vm.completedTasks.remove(task);
        }else{
            displayTasks(tasksDataArray);
        }
    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクを削除できません\n" + e);
    });
}

//タスク完了状態チェックボタン有効化
function enableTaskStatusChangeButton(task){
    let project_id = null;
    if($(".selected_column").hasClass("main-column-projects-project")){
        project_id = $(".selected_column").data("project_id");
    }
    //タスクを完了状態にしたときのみ効果音を鳴らす
    if(!task.isCompleted && soundNum !== 0){
        sound.play();
    }
    //Ajax
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: project_id,
                task_id: task.id,
                task_status: 1 - task.status,
                todo: "updateTaskStatus"
            },
            dataType: "json"
        }
    ).done(function(tasksDataArray){
        //返ってきたタスク処理する
        displayTasks(tasksDataArray);
    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクの状態を変更できません\n" + e);
    });
}

//プロジェクトの一覧を取得して表示する
function displayProjects(projectsDataArray){
    for(let i=0; i<projectsDataArray.length; i++){
        const project_id = projectsDataArray[i]["project_id"];
        const project_name = projectsDataArray[i]["project_name"];
        let color = projectsDataArray[i]["color"];
        if(color === null) color = '#777777';
        vm.projects.push(new Project(project_id, project_name, color));
    }
}

//プロジェクトの一覧初期表示
function initProjects(){
    //プロジェクト一覧を表示
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                todo: "selectProject"
            },
            dataType: "json"
        }
    ).done(function(projectsDataArray){
        //返ってきたプロジェクト処理する
        displayProjects(projectsDataArray);
        //今日のタスク表示
        clickToday();
    }).fail(function(XMLHttpRequest, status, e){
        alert("プロジェクトを表示できません\n" + e);
    });
}

//プロジェクト入力欄選択中に、Enterを押した際
function inputProject(text){
    $.ajax("./post.php",  
        {
            type: "POST",
            data:{
                value: text,
                todo: "insertProject"
            },
            dataType: "json"
        }
    ).done(function(data){
        //追加時、入力したテキストボックスを空にする
        $("#input_project").val("");
        //プロジェクトの末尾に追加
        for (let i=0; i < data.length; i++){
            let color = data[i]["color"];
            if(color === null) color = '#777777';
            vm.projects.push(new Project(data[i]["project_id"], data[i]["project_name"], color));
        }
    }).fail(function(XMLHttpRequest, status, e){
        alert("入力に失敗しました\n" + e);
    });
}

//プロジェクトの色変更
function changeProjectColor(project, event){
    const color = event.target.value;
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: project.id,
                color: color,
                todo: "updateProjectColor"
            },
            dataType: "json"
        }
    ).done(function(data){
        if(data != 1){
            alert("プロジェクト色変更できませんでした");
            return;
        }
        project.color = color;
    }).fail(function(XMLHttpRequest, status, e){
        alert("プロジェクト色変更できませんでした" + e);
    });
}

//プロジェクトの削除
function deleteProject(project){
    //確認ダイアログ
    if(window.confirm("プロジェクトを削除してもよろしいですか？")){
        $.ajax("./post.php",
            {
                type: "POST",
                data:{
                    project_id: project.id,
                    todo: "deleteProject"
                },
                dataType: "json"
            }
        ).done(function(data){
            //削除プロジェクトが選択中か確認する
            let selectedColumn = $(".selected_column");
            let selectedColumnId;
            let isDeletedProjectWasSelected = false;
            if(selectedColumn.hasClass("main-column-projects-project")){
                selectedColumnId = String(selectedColumn.data("project_id"));
                if(data === selectedColumnId){
                    isDeletedProjectWasSelected = true;
                }
            }
            //削除したプロジェクトを画面表示から消す
            vm.projects.remove(project);
            //削除プロジェクトが選択中だった場合、タスク画面表示を変更する
            if(isDeletedProjectWasSelected){
                vm.incompleteTasks.removeAll();
                vm.completedTasks.removeAll();
                $("#todo_title").text("プロジェクト未選択");
                $(".main-todo-body-tasks-task").addClass("hidden");
            }
        }).fail(function(XMLHttpRequest, status, e){
            alert("削除できませんでした" + e);
        });
    }
}

//タスク入力欄選択中にEnter
function inputTask(text){
    //日付未入力の場合、表示をして終了
    if($("#input_date").val() == false){
        alert("日付を入力してください");
        return;
    }
    //プロジェクト選択中なら、プロジェクトのIDとタスクの内容と、期限を取得
    let selectedColumn = $(".selected_column");
    if(selectedColumn.hasClass("main-column-projects-project")){
        let project_id = selectedColumn.data("project_id");
        let completetion_date = $("#input_date").val();
        //AjaxでPOSTする
        $.ajax("./post.php",
            {
                type: "POST",
                data:{
                    project_id: project_id,
                    task_value: text,
                    completetion_date: completetion_date,
                    todo: "insertTaskToProject"
                },
                dataType: "json"
            }
        ).done(function(tasksDataArray){
            //入力したタスク名を空白にする
            $("#input_task").val("");
            displayTasks(tasksDataArray);
        }).fail(function(XMLHttpRequest, status, e){
            alert("タスクを追加できませんでした\n" + e);
        });
    
    }else{
        alert("タスクの追加対象プロジェクトが選択されていません");
    }  
}

//日付入力欄に初期値（今日）を設定
function initTaskDate(){
    let todayDate = new Date();
    let year = todayDate.getFullYear();
    let month = todayDate.getMonth() + 1;
    //0詰めにする
    if(month < 10){
        month = "0" + month;
    }
    let day = todayDate.getDate();
    if(day < 10){
        day = "0" + day;
    }
    //フォームの初期値を表示
    let todayInit = year + "-" + month + "-" + day;
    $("#input_date").val(todayInit);
}

//今日ボタン
function clickToday(){
    $(".selected_column").removeClass("selected_column");
    $("#today_task").addClass("selected_column");
    removeHiddenClassOfTaskForm();
    let selectedColumnName = "今日";
    $("#todo_title").text(selectedColumnName);
    displayTasksByDayColumn("today");
}

//明日ボタン
function clickTomorrow(){
    $(".selected_column").removeClass("selected_column");
    $("#tomorrow_task").addClass("selected_column");
    removeHiddenClassOfTaskForm();
    let selectedColumnName = "明日";
    $("#todo_title").text(selectedColumnName);
    displayTasksByDayColumn("tomorrow");
}
//それ以降ボタン
function clickLater(){
    $(".selected_column").removeClass("selected_column");
    $("#later_task").addClass("selected_column");
    removeHiddenClassOfTaskForm();
    let selectedColumnName = "それ以降";
    $("#todo_title").text(selectedColumnName);
    displayTasksByDayColumn("later");
}

//完了済みボタン
function clickCompleted(){
    $(".selected_column").removeClass("selected_column");
    $("#completed_task").addClass("selected_column");
    removeHiddenClassOfTaskForm();
    //タスク画面で、選択中のプロジェクトタイトルを変更
    let selectedColumnName = "完了済み";
    $("#todo_title").text(selectedColumnName);
    displayTasksByDayColumn("completed");
}

//未完了タスクの表示
function clickIncomplete(){
    $(".selected_column").removeClass("selected_column");
    $("#incomplete_task").addClass("selected_column");
    removeHiddenClassOfTaskForm();
    let selectedColumnName = "期限超過";
    $("#todo_title").text(selectedColumnName);
    displayTasksByDayColumn("incomplete");
}

//タスク入力フォームを非表示にする
function removeHiddenClassOfTaskForm(){
    if(!$(".main-todo-body-tasks-task").hasClass("hidden")){
        $(".main-todo-body-tasks-task").addClass("hidden");
    }
}

//完了済みタスクの表示非表示 
function openComplete(){
    vm.openCompleteTaskFlag = 1 - vm.openCompleteTaskFlag();
}
