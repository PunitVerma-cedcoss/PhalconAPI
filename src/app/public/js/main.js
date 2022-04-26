var token = ''
token = String(document.cookie.substring(6, document.cookie.length))
setTimeout(() => {

    if (document.cookie.length > 6) {
        $(".enter-token").toggleClass("hidden")
        $(".add-order").toggleClass("hidden")

    }
    $(".addOrder").click(function (e) {
        e.preventDefault();
        var name = $("#name").val().trim()
        var productId = $("#productId").val().trim()
        var varient = $("#varient").val().trim() ?? ''
        if (name != '' && productId != '') {
            addOrder(name, productId, varient)
        }
    });


    function addOrder(name, productId, varient) {
        $.ajax({
            type: "post",
            url: "http://192.168.2.2:8080/api/order/create",
            contentType: 'application/json',
            headers: {
                'bearer': token
            },
            data: JSON.stringify({
                'customer name': name,
                'product id': productId,
                'varient': varient
            }),
            success: function (response) {
                console.log(response)
                addNoti(response.msg, "s")
            },
            error: function (xhr, status, error) {
                console.log("error--")
                addNoti(xhr.responseJSON.msg)
            }
        });
    }

    $(".setToken").click(function (e) {
        e.preventDefault();
        var t = $(".getToken").val().trim()
        if (t != '') {
            setCookie("token", t, 1)
            // make token hidden
            $(".enter-token").toggleClass("hidden")
            $(".add-order").toggleClass("hidden")
        }
    });

    function setCookie(cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        location.reload()
    }


    $("body").on("click", ".close", function () {
        $(this).fadeOut()
    });

    // handle tabs
    $(".addTab").click(function (e) {
        e.preventDefault();
        $(".add-order").addClass("hidden")
        $(".update-order").addClass("hidden")
        $(".view-product").addClass("hidden")
        $(".update-product").addClass("hidden")
        $(".view-order").removeClass("hidden")
    });

    $(".viewTab").click(function (e) {
        e.preventDefault();
        $(".view-order").addClass("hidden")
        $(".update-order").addClass("hidden")
        $(".view-product").addClass("hidden")
        $(".add-order").removeClass("hidden")

    });

    $(".createProducts").click(function (e) {
        e.preventDefault();
        $(".view-order").addClass("hidden")
        $(".update-order").addClass("hidden")
        $(".view-product").addClass("hidden")
        $(".add-order").addClass("hidden")
        $(".create-product").removeClass("hidden")
    });

    $(".viewProducts").click(function (e) {
        e.preventDefault();
        $(".view-order").addClass("hidden")
        $(".update-order").addClass("hidden")
        $(".add-order").addClass("hidden")
        $(".view-product").removeClass("hidden")
    });


    function renderOrders() {
        $.ajax({
            type: "get",
            url: "http://192.168.2.2:8080/api/order/get",
            headers: {
                'bearer': token
            },
            success: function (response) {
                console.log(response)
                if (response.status == 200) {
                    addNoti(response.status, "s")
                    renderTable(response.data)
                }
                else {
                    addNoti("some error occured", "w")
                }
            },
            error: function (xhr, status, error) {
                console.log("error--")
                addNoti(xhr.responseJSON.msg)
                $(".view-order").append(
                    `
                    <p class="text-5xl text-gray-800 font-black">You are not Authorised or Admin</p>
                    `
                )
            }
        });
    }
    renderOrders()


    function renderProducts() {
        $.ajax({
            type: "get",
            url: "http://192.168.2.2:8080/api/products/get",
            headers: {
                'bearer': token
            },
            success: function (response) {
                console.log(response)
                console.log("got products--")
                if (response.status == 200) {
                    addNoti(response.status, "s")
                    renderProductsTable(response.data)
                }
                else {
                    addNoti("some error occured", "w")
                }
            },
            error: function (xhr, status, error) {
                console.log("error--")
                addNoti(xhr.responseJSON.msg)
                $(".view-product").append(
                    `
                    <p class="text-5xl text-gray-800 font-black">You are not Authorised or Admin</p>
                    `
                )
            }
        });
    }
    renderProducts()


    function renderTable(data) {
        $(".view-order").empty()
        var m = `
    <table class="text-gray-800 bg-white shadow-lg">
    <thead>
    <tr>
    <th class="p-3 border">Customer name</th>
    <th class="p-3 border">Order Date</th>
    <th class="p-3 border">Product id</th>
    <th class="p-3 border">Varient</th>
    </tr>
    <tbody>
    `
        data.forEach(i => {
            m += `
        <tr data="${i["_id"]["$oid"]}" class="update odd:bg-white even:bg-gray-100 cursor-pointer hover:bg-rose-200 active:bg-rose-500">
            <td class="p-3 border">${i["customer name"]}</td>
            <td class="p-3 border">${i["order date"]["$date"]["$numberLong"]}</td>
            <td class="p-3 border">${i["product id"]}</td>
            <td class="p-3 border">${i["varient"] ?? 'no varient'}</td>
        </tr>
        `
        });
        m += `
    </tbody>
    </table>
    `
        $(".view-order").append(m)
    }
    function renderProductsTable(data) {
        $(".view-product").empty()
        var m = `
    <table class="text-gray-800 bg-white shadow-lg">
    <thead>
    <tr>
    <th class="p-3 border">Product name</th>
    <th class="p-3 border">Product Price</th>
    <th class="p-3 border">Product Stock</th>
    <th class="p-3 border">Category name</th>
    </tr>
    <tbody>
    `
        data.forEach(i => {
            m += `
        <tr data="${i["_id"]["$oid"]}" class="update-product-tr odd:bg-white even:bg-gray-100 cursor-pointer hover:bg-rose-200 active:bg-rose-500">
            <td class="p-3 border">${i["product name"]}</td>
            <td class="p-3 border">${i["product price"]}</td>
            <td class="p-3 border">${i["product stock"]}</td>
            <td class="p-3 border">${i["category name"]}</td>
        </tr>
        `
        });
        m += `
    </tbody>
    </table>
    `
        $(".view-product").append(m)
    }
    // handle update
    $("body").on("click", ".update", function () {
        var id = $(this).attr("data").trim()
        if (id != '') {

            $(".view-order").addClass("hidden")
            $(".update-order").removeClass("hidden")

            //populate fields
            $("#updateName").val($(this).children().eq(0).text())
            $("#updateProductId").val(id)
            $("#updateVarient").val($(this).children().eq(3).text())

        }
    });

    // handle product update
    $("body").on("click", ".update-product-tr", function () {
        $(".view-order").addClass("hidden")
        $(".update-order").addClass("hidden")
        $(".add-order").addClass("hidden")
        $(".view-product").addClass("hidden")
        $(".update-product").removeClass("hidden")

        //populate fields
        $("#productName").val($(this).children().eq(0).text())
        $("#productPrice").val($(this).children().eq(1).text())
        $("#productStock").val($(this).children().eq(2).text())
        $("#updateProduct").attr("pid", $(this).attr("data"))



    });

    $(".updateOrder").click(function (e) {
        e.preventDefault();
        var name = $("#updateName").val().trim()
        var productId = $("#updateProductId").val().trim()
        var varient = $("#updateVarient").val().trim() ?? ''
        if (name != '' && productId != '') {
            updateOrder(name, productId, varient)
        }
    });
    function updateOrder(name, productId, varient) {
        $.ajax({
            type: "put",
            url: "http://192.168.2.2:8080/api/order/update",
            contentType: 'application/json',
            headers: {
                'bearer': token
            },
            data: JSON.stringify({
                'product id': productId,
                'data': {
                    'customer name': name,
                    'varient': varient
                }
            }),
            success: function (response) {
                console.log(response)
                addNoti(response.msg, "s")
                renderOrders()
                $(".view-order").removeClass("hidden")
                $(".update-order").addClass("hidden")
            },
            error: function (xhr, status, error) {
                console.log("error--")
                addNoti(xhr.responseJSON.msg)
            }
        });
    }
    $("#updateProduct").click(function (e) {
        e.preventDefault();
        var id = $("#updateProduct").attr("pid").trim()
        var productName = $("#productName").val().trim()
        var productPrice = $("#productPrice").val().trim()
        var productStock = $("#productStock").val().trim() ?? ''
        if (id != '' && productName != '' && productPrice != '' && productStock != '') {
            updateProduct(id, productName, productPrice, productStock)
        }
    });
    function updateProduct(id, productname, productPrice, productStock) {
        $.ajax({
            type: "put",
            url: "http://192.168.2.2:8080/api/products/update",
            contentType: 'application/json',
            headers: {
                'bearer': token
            },
            data: JSON.stringify({
                "productId": id,
                "data": {
                    'product name': productname,
                    'product price': productPrice,
                    'product stock': productStock,
                }
            }),
            success: function (response) {
                console.log(response)
                addNoti(response.msg, "s")
                renderOrders()
                $(".view-order").removeClass("hidden")
                $(".update-order").addClass("hidden")
            },
            error: function (xhr, status, error) {
                console.log("error--")
                addNoti(xhr.responseJSON.msg)
            }
        });
    }

}, 1000);


$("#createProduct").click(function (e) {
    e.preventDefault();
    var productName = $("#createProductName").val().trim()
    var productCategory = $("#createProductcategory").val().trim()
    var productPrice = $("#createProductPrice").val().trim() ?? ''
    var productStock = $("#createProductStock").val().trim() ?? ''
    if (productName != '' && productCategory != '' && productPrice != '' && productStock != '') {
        addProduct(productName, productCategory, productPrice, productStock)
    }
});

function addProduct(productName, productCategory, productPrice, productStock) {
    $.ajax({
        type: "post",
        url: "http://192.168.2.2:8080/api/products/create",
        contentType: 'application/json',
        headers: {
            'bearer': token
        },
        data: JSON.stringify({
            'product name': productName,
            'category name': productCategory,
            'product price': productPrice,
            'product stock': productStock,
            'metas': {},
            'varient': {}
        }),
        success: function (response) {
            console.log(response)
            addNoti(response.msg, "s")
        },
        error: function (xhr, status, error) {
            console.log("error--")
            addNoti(xhr.responseJSON.msg)
        }
    });
}


function addNoti(msg, type = "w") {
    var m = `
    <div class="close ${type == 'w' ? 'bg-rose-500' : 'bg-green-500'} cursor-pointer text-white p-3 m-2 rounded-lg shadow-lg">
    <p>${msg}</p>
    </div>
    
    `
    $(".noti").append(m)
}
