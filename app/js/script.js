$(document).ready(function() {
    console.log("Snack Shop System Ready!");

    // 1. Button Hover Effect
    $(".btn, .btn-blue").hover(
        function() { $(this).css("opacity", "0.8"); },
        function() { $(this).css("opacity", "1"); }
    );

    // 2. Image Preview Logic 
    // This will only run if an element with id="photo" exists on the page
    $(document).on("change", "#photo", function() {
        const file = this.files[0]; 
        if (file) {
            // This updates ANY element with id="preview" 
            // OR the <img> inside the .upload label
            $("#preview, .upload img").attr("src", URL.createObjectURL(file));
        }
    });

    // 3. Auto-hide error messages
    setTimeout(function() {
        $(".err").fadeOut("slow");
    }, 5000);

    const selected = $("input[name='payment_method']:checked").val();

    if (selected === "Card") {
        showForm("card");
    } else if (selected === "Online") {
        showForm("online");
    }
});