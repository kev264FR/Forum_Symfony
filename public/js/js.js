$("#logo").click(function(){
    $("*").toggleClass("vibrate-1")
})

$.confirm.options = {
    text: "Cette action est définitive, voulez vous continuer ?",
    title: "",
    confirmButton: "Oui continuer",
    cancelButton: "Non, revenir en arrière",
    post: false,
    submitForm: false,
    confirmButtonClass: "btn-warning",
    cancelButtonClass: "btn-secondary",
    dialogClass: "modal-dialog"
}

$(".confirm").confirm()

$("#test").confirm()