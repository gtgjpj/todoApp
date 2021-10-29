/**
 * //サウンドの変更
 * @function changeSound
 * //表示関係
 * //コラム・プロジェクトのタスクをDBから取得し一覧表示する
 * @function displayTasks
 * 
 * //タスクのボタン有効化
 * //タスク削除クリック処理
 * @function clickTaskDelete
 * //タスクチェッククリック処理
 * @function clickTaskStatus
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
 * //表示、非表示処理
 * //完了済みタスクの表示非表示 
 * @function openComplete
 */

//コラム配列(vm.columns)インデックス
const COLUMN_TYPE = { TODAY: 0, TOMORROW: 1, LATER: 2, COMPLETED: 3, INCOMPLETE: 4 };
//日付範囲(昨日、今日、明日、明後日)
const DATE_RANGE = { YESTERDAY: -1, TODAY: 0, TOMORROW: 1, DAY_AFTER_TOMORROW: 2 };
//タスクステータス(0:完了済み、1:未完了)
const TASK_STATUS = { COMPLETED: 0, INCOMPLETE: 1 };
//デフォルトプロジェクト色
const DEFAULT_PROJECT_COLOR = "#777777";
//デフォルトフォントファミリー
const DEFAULT_FONT_FAMILY = "unset";

//ビューモデルクラス
class ViewModel {
    // コラム・プロジェクト(画面左側)
    columns = [
        new Column("今日", "#66cdaa", "wb_sunny", DATE_RANGE.TODAY, DATE_RANGE.TODAY, null),
        new Column("明日", "#ffa500", "wb_twilight", DATE_RANGE.TOMORROW, DATE_RANGE.TOMORROW, null),
        new Column("それ以降", "#1e90ff", "date_range", DATE_RANGE.DAY_AFTER_TOMORROW, null, null),
        new Column("完了済み", "#777", "check_circle_outline", null, null, TASK_STATUS.COMPLETED),
        new Column("期限超過", "#ff5416cc", "new_releases", null, DATE_RANGE.YESTERDAY, null)
    ];
    projects = ko.observableArray();

    // タスク(画面右側)
    incompleteTasks = ko.observableArray();
    completedTasks = ko.observableArray();

    //コラム・プロジェクト一覧表示中であるか？
    displayColumn = ko.observable(true);

    //モバイル端末画面であるか？
    isMobile = ko.observable(false);

    //完了済みタスクの表示フラグ(0:非表示)
    openCompleteTaskFlag = ko.observable(0);

    //選択中のコラム・プロジェクト
    selectedColumn = ko.observable(null);

    //名称変更中のプロジェクト
    renameProject = ko.observable(null);

    //設定ウィンドウ表示中であるか？
    displaySettings = ko.observable(false);

    /*** 設定 ***/
    //設定アイコン表示・非表示
    displaySettingsIcon = ko.observable(false);
 
    //背景画像
    imageFileMaxSize = ko.observable('0');
    backgroundImage = ko.observable(null);

    //フォント
    availableFontFamilies = [
        new FontFamily("デフォルト", "unset"),
        new FontFamily("Zen Antique", "'Zen Antique'"),
        new FontFamily("Rampart One", "'Rampart One'"),
        new FontFamily("Hachi Maru Pop", "'Hachi Maru Pop'")
    ];
    selectedFontFamily = ko.observable(DEFAULT_FONT_FAMILY);
}

//コラム抽象クラス(モデル)
class AbstractColumn {
    name = ko.observable('');
    color = ko.observable(DEFAULT_PROJECT_COLOR);
    icon = '';

    /**
     * コンストラクタ
     * @param name:名称
     * @param color:ラベル色・アイコン色
     * @param icon:アイコン(Google Fonts)
     */
    constructor(name, color, icon){
        this.name(name);
        this.color(color);
        this.icon = icon;
    }

    //displayTasks()条件項目
    get projectId(){ return null; }
    get completetionDateRangeStart(){ return null; }
    get completetionDateRangeEnd(){ return null; }
    get taskStatus(){ return null; }
}

//プロジェクトクラス(モデル)
class Project extends AbstractColumn {
    id = null;

    /**
     * コンストラクタ
     * @param id:プロジェクトID
     * @param name:プロジェクト名称
     * @param color:ラベル色
     */
    constructor(id, name, color){
        super(name, color, 'label');
        this.id = id;
    }

    //displayTasks()条件項目(オーバーライド)
    get projectId(){ return this.id; }
}

//コラムクラス(モデル)
class Column extends AbstractColumn {
    #completetionDateRangeStart = null;
    #completetionDateRangeEnd = null;
    #taskStatus = null;
    countIncompleteTasks = ko.observable();

    /**
     * コンストラクタ
     * @param name:コラム名称
     * @param color:アイコン色
     * @param icon:アイコン(Google Fonts)
     * @param completetionDateRangeStart:表示するタスク期限日(期間開始日, DATE_RANGEを使用)
     * @param completetionDateRangeEnd:表示するタスク期限日(期間終了日, DATE_RANGEを使用)
     * @param taskStatus:表示するタスクステータス(0:完了済み, 1:未完了)
     */
    constructor(name, color, icon, completetionDateRangeStart, completetionDateRangeEnd, taskStatus){
        super(name, color, icon);
        this.#completetionDateRangeStart = completetionDateRangeStart;
        this.#completetionDateRangeEnd = completetionDateRangeEnd;
        this.#taskStatus = taskStatus;
    }

    //daysLater日後の日付文字列を取得する(プライベートメソッド)
    daysLaterToDateStr(daysLater){
        if(daysLater === null || typeof(daysLater)!= "number") return null;
        const d = new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * daysLater));
        return `${d.getFullYear()}-${d.getMonth()+1}-${d.getDate()}`;
    }

    //displayTasks()条件項目(オーバーライド)
    get completetionDateRangeStart(){
        return this.daysLaterToDateStr(this.#completetionDateRangeStart);
    }
    get completetionDateRangeEnd(){
        return this.daysLaterToDateStr(this.#completetionDateRangeEnd);
    }
    get taskStatus(){ return this.#taskStatus; }
}

//タスククラス(モデル)
class Task{
    id = -1;
    project = null;
    value = '';
    completetionDate = '9999-12-31';
    status = 1;
    constructor(id, project, value, completetionDate, status){
        this.id = id;
        this.project = project;
        this.value = value;
        this.completetionDate = completetionDate;
        this.status = status;
    }

    //完了したか？
    get isCompleted(){ return this.status == TASK_STATUS.COMPLETED; }

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

//ヘッダー用ビューモデルクラス
class HeadViewModel {
    //フォントファミリー(Google Fonts読み込み用)
    fontFamilyHref = ko.computed(function(){
        if(vm.selectedFontFamily() === "unset") {
            return "";
        }
        const param = vm.selectedFontFamily().replaceAll("\'", "").replaceAll(" ", "+");
        return `https://fonts.googleapis.com/css2?family=${param}&display=swap`;
    });
}

//フォントファミリー(ユーザー設定用)
class FontFamily {
    name = '';
    fontFamily = '';
    constructor(name, fontFamily){
        this.name = name;
        this.fontFamily = fontFamily;
    }
}

//ビューにビューモデルをバインド
const vm = new ViewModel();
vm.isMobile(navigator.userAgent.match(/iPhone|Android.+Mobile/));
vm.backgroundImage.subscribe(function(newValue){
    changeBackgroundColorAlpha(".header", newValue !== null ? 0.9375 : 1.0);
    changeBackgroundColorAlpha(".settings", newValue !== null ? 0.9375 : 1.0);
    changeBackgroundColorAlpha(".main", newValue !== null ? 0.75 : 1.0);
    changeBackgroundColorAlpha(".main-column", newValue !== null ? 0.0 : 1.0);
    changeBackgroundColorAlpha(".column_box", newValue !== null ? 0.75 : 1.0);
    changeBackgroundColorAlpha(".selected_column", newValue !== null ? 0.75 : 1.0);
    changeBackgroundColorAlpha(".main-column-projects-project", newValue !== null ? 0.75 : 1.0);
    changeBackgroundColorAlpha(".main-todo", newValue !== null ? 0.0 : 1.0);
    changeBackgroundColorAlpha(".main-todo-result", newValue !== null ? 0.75 : 1.0);
    changeBackgroundColorAlpha(".main-todo-body-tasks-task", newValue !== null ? 0.75 : 1.0);
    changeBackgroundColorAlpha(".main-todo-body-complete_task_button", newValue !== null ? 0.75 : 1.0);
    changeBackgroundColorAlpha(".main-todo-body-complete_tasks", newValue !== null ? 0.75 : 1.0);
    changeBackgroundColorAlpha(".main-todo-body-incomplete_tasks", newValue !== null ? 0.75 : 1.0);
});
ko.applyBindings(vm);

//ヘッダーにヘッダー用ビューモデルをバインド
const headViewModel = new HeadViewModel();
ko.applyBindings(headViewModel, $("head")[0]);

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

    //サウンドボタン有効化
    document.getElementById("sound").addEventListener("click", function(){
        //サウンドの切り替え
        soundNum = (soundNum + 1) % 3;
        changeSound();
    });

    //スキン初期設定
    initSkin();

    //未完了タスク数の表示
    displayIncompleteTasks();
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
 * @function コラム・プロジェクトのタスクをDBから取得し一覧表示する
 * @param column:対象コラム・プロジェクト
 */
function displayTasks(column){
    //タスク一覧を表示
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: column.projectId,
                date_range_start: column.completetionDateRangeStart,
                date_range_end: column.completetionDateRangeEnd,
                status: column.taskStatus,
                todo: "selectTasks"
            },
            dataType: "json"
        }
    ).done(function(tasksDataArray){
        //返ってきたタスク処理する
        vm.selectedColumn(column);
        vm.incompleteTasks.removeAll();
        vm.completedTasks.removeAll();
        for(const row of tasksDataArray){
            const task = readTaskFromRow(row);
            if(task.isCompleted){
                //完了済みタスク
                vm.completedTasks.push(task);
            }else{
                //未完了タスク
                vm.incompleteTasks.push(task);
            }
        }
    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクを表示できません\n" + e);
    });
    return true;
}

/**
 * @function 指定のタスクの削除ボタンを有効化する
 * ボタンをクリックした際、プロジェクト選択時はそのid（異なる場合はNull）を送る
 * データベースからタスクの情報を削除し、表示を更新する
 * @param task_id_data:dataオブジェクト
 */
function clickTaskDelete(task){
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                task_id: task.id,
                todo: "deleteTask"
            },
            dataType: "json"
        }
    ).done(function(data){
        if(data == 0){
            alert("タスクはすでに削除されています");
        }
        //タスクを除去する
        vm.incompleteTasks.remove(task);
        vm.completedTasks.remove(task);
        //未完了タスク数が変更される可能性があるため、更新する
        displayIncompleteTasks();
    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクを削除できません\n" + e);
    });
}

//タスク完了状態チェックボタン有効化
function clickTaskStatus(task){
    const project_id = (vm.selectedColumn() instanceof Project) ? vm.selectedColumn().id : null;
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
        displayTasks(vm.selectedColumn());
        //未完了タスク数が変更される可能性があるため、更新する
        displayIncompleteTasks();
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
        if(color === null) color = DEFAULT_PROJECT_COLOR;
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
        displayTasks(vm.columns[COLUMN_TYPE.TODAY]);
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
    ).done(function(project_id){
        if(project_id <= 0){
            alert("入力に失敗しました");
            return;
        }
        const project_name = $("#input_project").val();
        //追加時、入力したテキストボックスを空にする
        $("#input_project").val("");
        //プロジェクトの末尾に追加し、選択状態にする
        const project = new Project(project_id, project_name, DEFAULT_PROJECT_COLOR);
        vm.projects.push(project);
        displayTasks(project);
        
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
                todo: "updateProject"
            },
            dataType: "json"
        }
    ).done(function(data){
        if(data != 0 && data != 1){
            alert("プロジェクト色変更できませんでした");
            return;
        }
        project.color(color);
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
            //削除したプロジェクトを画面表示から消す
            vm.projects.remove(project);
            //削除プロジェクトが選択中だった場合、タスク画面表示を変更する
            vm.incompleteTasks.remove(function(item){ return item.project === project } );
            vm.completedTasks.remove(function(item){ return item.project === project } );
            if(vm.selectedColumn() === project){
                vm.selectedColumn(null);
            }
            //未完了タスク数が変更される可能性があるため、更新する
            displayIncompleteTasks();
        }).fail(function(XMLHttpRequest, status, e){
            alert("削除できませんでした" + e);
        });
    }
}

//実際の未完了タスク数を取得して反映させる
//左上メニューの値を表示
function displayIncompleteTasks(){
    //今日、明日、明後日の日付を取得する
    let dateToday = new Date();
    let timestampTomorrow = new Date().getTime() + (1000 * 60 * 60 * 24 * 1);
    let dateTomorrow = new Date(timestampTomorrow);
    let timestampLater = new Date().getTime() + (1000 * 60 * 60 * 24 * 2);
    let dateLater = new Date(timestampLater);
    
    let todayStr = dateToday.getFullYear() + "-" + (dateToday.getMonth() + 1) + "-" + dateToday.getDate();
    let tomorrowStr = dateTomorrow.getFullYear() + "-" + (dateTomorrow.getMonth() + 1) + "-" + dateTomorrow.getDate();
    let laterStr = dateLater.getFullYear() + "-" + (dateLater.getMonth() + 1) + "-" + dateLater.getDate();
    //ajaxで今日、明日、それ以降、期限超過タスク数をもってくる
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                today: todayStr,
                tomorrow: tomorrowStr,
                later: laterStr, 
                todo: "selectIncompleteTasksCount"
            },
            dataType: "json"
        }
    ).done(function(incompleteTasksCount){
        vm.columns[COLUMN_TYPE.TODAY].countIncompleteTasks(incompleteTasksCount[0]["count"]);
        vm.columns[COLUMN_TYPE.TOMORROW].countIncompleteTasks(incompleteTasksCount[1]["count"]);
        vm.columns[COLUMN_TYPE.LATER].countIncompleteTasks(incompleteTasksCount[2]["count"]);
        vm.columns[COLUMN_TYPE.INCOMPLETE].countIncompleteTasks(incompleteTasksCount[3]["count"]);
    }).fail(function(e){
        alert("未完了タスク数を表示できません\n" + e);
    });
}

//タスク入力欄選択中にEnter
function inputTask(text){
    //プロジェクト選択中でない場合はreturn
    const column = vm.selectedColumn();
    if(!(column instanceof Project)){
        alert("タスクの追加対象プロジェクトが選択されていません");
        return;
    }

    //日付未入力の場合、表示をして終了
    if($("#input_date").val() == false){
        alert("日付を入力してください");
        return;
    }
    //プロジェクトのIDとタスクの内容と期限を取得
    const completetion_date = $("#input_date").val();
    //AjaxでPOSTする
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: column.id,
                task_value: text,
                completetion_date: completetion_date,
                todo: "insertTaskToProject"
            },
            dataType: "json"
        }
    ).done(function(tasksDataArray){
        //入力したタスク名を空白にする
        $("#input_task").val("");
        displayTasks(vm.selectedColumn());
        //未完了タスク数が変更されるため、更新する
        displayIncompleteTasks();
    }).fail(function(XMLHttpRequest, status, e){
        alert("タスクを追加できませんでした\n" + e);
    });
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

//完了済みタスクの表示非表示 
function openComplete(){
    vm.openCompleteTaskFlag(1 - vm.openCompleteTaskFlag());
}

//プロジェクト名クリック
function clickProjectName(project, event){
    if(vm.selectedColumn() !== project) return true;
    $(event.target).parent().find(".rename").val(project.name());
    vm.renameProject(project);
    return true;
}

//プロジェクト名変更 Enterキー押下時の処理
function keydownRenameProject(project, event){
    if(vm.renameProject() === null) return true;
    if(event.key !== "Enter") return true;
    const project_name = event.target.value;
    if(project_name.length <= 0) {
        alert('プロジェクトを入力してください');
        return true;
    }
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                project_id: project.id,
                project_name: project_name,
                todo: "updateProject"
            },
            dataType: "json"
        }
    ).done(function(data){
        if(data != 0 && data != 1){
            alert("プロジェクトを変更できませんでした");
            return;
        }
        project.name(project_name);
        vm.renameProject(null);
    }).fail(function(XMLHttpRequest, status, e){
        alert("プロジェクトを変更できませんでした" + e);
    });
    return true;
}

//プロジェクト名変更 フォーカスが外れた時の処理
function blurProjectName(project, event){
    vm.renameProject(null);
    return true;
}

//バックエンドから返されたタスク配列の要素を読み込みTaskオブジェクトを作成
function readTaskFromRow(row){
    //各項目を取得する
    const task_id = row["task_id"];
    const project_id = row["project_id"];
    const task_value = row["task_value"];
    const completetion_date = row["completetion_date"];
    const task_status = row["task_status"];

    // プロジェクトIDより対応するプロジェクトオブジェクトを探す
    let project = null;
    vm.projects().forEach(function(v, i){
        if(v.id != project_id) return;
        project = v;
    });

    return new Task(task_id, project, task_value, completetion_date, task_status);
}

//フォントファミリー変更(スキン設定)
function changeFontFamily(context, event){
    if(!event.originalEvent){
        return true;
    }
    //スキン情報変更(フォントファミリー)
    const fontFamily = event.target.value;
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                key: "font-family",
                value: fontFamily,
                todo: "updateSkin"
            },
            dataType: "json"
        }
    ).done(function(data){
        if(data !== true){
            alert("フォントを変更できません");
            return;
        }
    }).fail(function(XMLHttpRequest, status, e){
        alert("フォントを変更できません\n" + e);
    });
    return true;
}

//CSSセレクタからCSSルールを取得する
function getCssRules(selector){
    let result = [];
    const sheets = document.styleSheets;
    for(const sheet of sheets){
        let rules = null;
        try{
            rules = sheet.cssRules;
        }catch(e){
            continue;
        }
        for(const rule of rules){
            if(rule.selectorText !== selector) continue;
            result.push(rule);
        }
    }
    return result;
}

//CSSセレクタの背景色透明度を設定する
function changeBackgroundColorAlpha(selector, alpha){
    const cssRules = getCssRules(selector);
    for(const cssRule of cssRules){
        const style = cssRule.style;
        let color = style.backgroundColor;
        //プレフィックスが"rgb"または"rgba"の場合のみ透過設定処理を行う
        if(!color.startsWith('rgb(') && !color.startsWith('rgba(')){
            continue;
        }
        //数字と","以外の文字を取り除いて、","で分割する
        //例: rgb(255, 255, 255) → ["255", "255", "255"]
        let rgba = color.replace(/[^\d,]/g, '').split(',');
        if(rgba.length < 3){
            continue;
        }

        if(rgba.length === 3){
            rgba.push(alpha);
        }else if(rgba.length === 4){
            rgba[3] = alpha;
        }
        style.backgroundColor = `rgba(${rgba[0]}, ${rgba[1]}, ${rgba[2]}, ${rgba[3]})`;
    }
}

//背景画像変更(スキン設定)
function updateBackgroundImage(imageData){
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                key: "background-image",
                value: imageData,
                todo: "updateSkin"
            },
            dataType: "json"
        }
    ).done(function(data){
        if(data !== true){
            alert("背景画像を変更できません");
            return;
        }
        vm.backgroundImage(imageData);
    }).fail(function(XMLHttpRequest, status, e){
        alert("背景画像を変更できません\n" + e);
    });
}
function changeBackgroundImage(object, event){
    const reader = new FileReader();
    reader.onload = function(e){
        //スキン情報変更(背景画像)
        updateBackgroundImage(e.target.result);
    }
    reader.readAsDataURL(event.target.files[0]);
}

//スキン初期設定
function initSkin(){
    //プロジェクト一覧を表示
    $.ajax("./post.php",
        {
            type: "POST",
            data:{
                todo: "selectSkin"
            },
            dataType: "json"
        }
    ).done(function(keyValueArray){
        if(keyValueArray === null){
            return;
        }
        let imageData = [];
        for(const row of keyValueArray){
            const key = row["key"];
            const value = row["value"];
            switch(key){
                case "font-family":
                    vm.selectedFontFamily(value);
                    break;
                case "image-file-max-size":
                    vm.imageFileMaxSize(value);
                    break;
                case "background-image":
                    imageData.push(value);
                    break;
            }
        }
        if(imageData.length>0){
            vm.backgroundImage(imageData.join(''));
        }
        vm.displaySettingsIcon(true);
    }).fail(function(XMLHttpRequest, status, e){
        console.log("スキン初期設定できません\n" + e);
    });
    return true;
}
