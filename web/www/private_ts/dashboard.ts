function main() {
    const template = document.getElementById("wishlist-item-template") as HTMLTemplateElement;
    var cloned = document.importNode(template.content, true);
    document.getElementById("my-lists")?.appendChild(cloned);
    console.log("Cloned element added");
}

//document.body.addEventListener("load", main);
main();
