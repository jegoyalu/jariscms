question_id = 1;
question_label = "Question";
answer_label = "Answer";

function add_question(question, answer)
{
    var question_value="";
    var answer_value="";

    if(question)
        question_value = 'value="'+question+'"';

    if(answer)
        answer_value = answer;

    row = '<tr style="width: 100%; border-bottom: solid 1px #d3d3d3; margin-bottom: 15px;" id="question-' + question_id + '">';

    row += '<td style="width: auto">';
    row += '<div style="padding-top: 7px; margin-bottom: 3px;"><input style="width: 90%;" placeholder="'+question_label+'" type="text" name="question_title[' + question_id + ']" '+question_value+' /></div>';
    row += '<div style="padding-bottom: 7px;"><textarea id="answer-'+question_id+'" style="width: 90%;" placeholder="'+answer_label+'" name="question_answer[' + question_id + ']">'+answer_value+'</textarea></div>';
    row += '</td>';

    row += "<td style=\"width: auto; text-align: center; vertical-align: center;\">";
    row += "<a href=\"javascript:remove_question(" + question_id + ")\">X</a>";
    row += "</td>";

    row += "</tr>";

    $("#questions-table > tbody").append($(row));

    if(typeof whizzywig == "object")
    {
        whizzywig.makeWhizzyWig("answer-"+question_id, "all");
    }

    question_id++;
}

function remove_question(id)
{
    $("#question-" + id).fadeOut("slow", function(){
        $(this).remove();
    });
}
