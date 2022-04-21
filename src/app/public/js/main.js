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

    function addNoti(msg, type = "w") {
        var m = `
        <div class="close ${type == 'w' ? 'bg-rose-500' : 'bg-green-500'} cursor-pointer text-white p-3 m-2 rounded-lg shadow-lg">
        <p>${msg}</p>
        </div>
        
        `
        $(".noti").append(m)
    }


    // handle tabs
    $(".addTab").click(function (e) {
        e.preventDefault();
        $(".add-order").addClass("hidden")
        $(".update-order").addClass("hidden")
        $(".view-order").removeClass("hidden")
    });

    $(".viewTab").click(function (e) {
        e.preventDefault();
        $(".view-order").addClass("hidden")
        $(".update-order").addClass("hidden")
        $(".add-order").removeClass("hidden")
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

}, 1000);