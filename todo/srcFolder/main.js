
window.onload = function(){
    //完了済みタスクの開閉
    document.getElementById("complete_task_button").addEventListener("click", openComplete);

    //左上リストのボタン機能
    document.getElementById("today_task").addEventListener("click", clickToday);
    document.getElementById("tomorrow_task").addEventListener("click", clickTomorrow);
    document.getElementById("later_task").addEventListener("click", clickLater);

    //プロジェクトの削除ボタン初期設定
    initProjectDeleteButton();
    
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
            }else if(current.id === "input_task"){
                let text = current.value;
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

    }).fail(function(XMLHttpRequest, status, e){
        alert(e);
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
    alert("task:" + text);
}

//今日ボタン
function clickToday(){
    alert("today");
}

//明日ボタン
function clickTomorrow(){
    alert("tomorrow");
}
//それ以降ボタン
function clickLater(){
    alert("later");
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
