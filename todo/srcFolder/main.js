

window.onload = function(){
    //完了済みタスクの開閉
    document.getElementById("complete_task_button").addEventListener("click", openComplete);

    //左上リストのボタン機能
    document.getElementById("today_task").addEventListener("click", clickToday);
    document.getElementById("tomorrow_task").addEventListener("click", clickTomorrow);
    document.getElementById("later_task").addEventListener("click", clickLater);
    document.getElementById("conpleted_task").addEventListener("click", clickCompleted);

    //プロジェクト選択機能初期化
    let projectElements = $(".main-column-projects-project");
    let projectCount = projectElements.length;
    projectElements.each(function(i, elem){
        $(elem).on("click",function(){
            $(".selected_column").removeClass("selected_column");
            $(elem).addClass("selected_column");
        })
    })

    //プロジェクトの削除ボタン初期設定
    initProjectDeleteButton();

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
        //新しく追加する要素の用意
        let newDiv = $(`<div data-project_id=${data[0]}></div>`);
        newDiv.addClass("main-column-projects-project");
        let newP = $("<p></p>");
        let newIcon1 = $("<i class='material-icons'>label</i>");
        let newIcon2 = $("<i class='material-icons delete_project_button'>delete_forever</i>")
        newP.html(data[1]);
        newDiv.append(newIcon1);
        newDiv.append(newP);
        newDiv.append(newIcon2);

        //プロジェクトの末尾に追加
        $("#projects").append(newDiv);
        //追加プロジェクトの削除ボタン有効化
        let newProject = $(".main-column-projects div:last");
        enableProjectDeleteButton(newProject);
        //追加プロジェクトの選択を有効化
        newProject.on("click", function(){
            $(".selected_column").removeClass("selected_column");
            newProject.addClass("selected_column");
        });
        

    }).fail(function(XMLHttpRequest, status, e){
        Swal.fire("日付を入力してください");
    });
}

//プロジェクトの初期ボタン有効化
function initProjectDeleteButton(){
    //プロジェクトの数
    let allProject = $(".main-column-projects-project");
    for(let i=1; i<=allProject.length; i++){
        let lastDeleteButton = $(`.main-column-projects div:nth-child(${i})`);
        enableProjectDeleteButton(lastDeleteButton);
    }
    
}

//プロジェクトの削除ボタン有効化
function enableProjectDeleteButton(element){
    let enableButton = element.find(".delete_project_button");
    enableButton.on("click", function(){
        let buttonId = element.attr("data-project_id");
        deleteProject(buttonId);
    })
}

//プロジェクトの削除
function deleteProject(id){
    //確認ダイアログ

    if(window.confirm("プロジェクトを削除してもよろしいですか？")){

        $.ajax("./post.php",
            {
                type: "POST",
                data:{
                    project_id: id,
                    todo: "deleteProject"
                },
                dataType: "json"
            }
        ).done(function(data){
            let deleteElement = $(`[data-project_id=${data}]`);
            deleteElement.remove();
        }).fail(function(XMLHttpRequest, status, e){
            alert(e);
        });
    }
}

//タスク入力欄選択中にEnter
function inputTask(text){
    if($("#input_date").val() == false){
        alert("日付を入力してください");
    }else{

    }
}

//日付入力欄に初期値（今日）を設定
function initTaskDate(){
    let todayDate = new Date();
    let year = todayDate.getFullYear();
    let month = todayDate.getMonth() + 1;
    if(month < 10){
        month = "0" + month;
    }
    let day = todayDate.getDate();
    if(day < 10){
        day = "0" + day;
    }

    let todayInit = year + "-" + month + "-" + day;
    $("#input_date").val(todayInit);
}

//今日ボタン
function clickToday(){
    $(".selected_column").removeClass("selected_column");
    $("#today_task").addClass("selected_column");
}

//明日ボタン
function clickTomorrow(){
    $(".selected_column").removeClass("selected_column");
    $("#tomorrow_task").addClass("selected_column");
}
//それ以降ボタン
function clickLater(){
    $(".selected_column").removeClass("selected_column");
    $("#later_task").addClass("selected_column");
}

//完了済みボタン
function clickCompleted(){
    $(".selected_column").removeClass("selected_column");
    $("#conpleted_task").addClass("selected_column");
}


//完了済みタスクの表示非表示 
const display_on_text = "完了済みのタスクを非表示";
const display_none_text = "完了済みのタスクを表示";
let openCompleteFlag = 0;
function openComplete(){
    let completeTaskDivs = document.getElementsByClassName("main-todo-body-complete_tasks");
    //非表示にする
    if(openCompleteFlag == 1){

        for(let i=0; i<completeTaskDivs.length; i++){
            completeTaskDivs[i].classList.add("hidden");
        }
        $("#complete_task_button").find("p").html(display_none_text);
    //表示させる
    }else{
        for(let i=0; i<completeTaskDivs.length; i++){
            completeTaskDivs[i].classList.remove("hidden");
        }
        $("#complete_task_button").find("p").html(display_on_text);
    }
    //フラグの切り替え
    openCompleteFlag = 1 - openCompleteFlag;
}
