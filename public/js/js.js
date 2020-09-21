$("#logo").click(function(){
    $("*").toggleClass("vibrate-1")
})

$.confirm.options = {
    text: "Cette action est définitive, voulez vous continuer ?",
    title: "",
    confirmButton: "Oui",
    cancelButton: "Non",
    post: false,
    submitForm: false,
    confirmButtonClass: "btn-danger",
    cancelButtonClass: "btn-info",
    dialogClass: "modal-dialog"
}

$(".confirm").confirm()