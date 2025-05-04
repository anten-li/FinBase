"use strict";
class baseApp {
    static createInput(name, value, type = "text", cl = "std-input") {
        var input = document.createElement('input');
        input.name = name;
        input.type = type;
        input.classList.add(cl);

        var lable = document.createElement('label');
        lable.innerHTML = value;
        lable.append(input);

        return lable;
    }

    static b64EncodeUnicode(str) {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, (match, p1) => {
            return String.fromCharCode('0x' + p1);
        }));
    }
}

class popupWin {
    bkelm;
    elm;
    elmTxt;
    constructor(text = "", buttons = ["ok"], onClose = (button, std) => { }) {
        this.bkelm = document.body.appendChild(document.createElement("div"));
        this.bkelm.classList.add("popupWin");

        this.elm = this.bkelm.appendChild(document.createElement("div"));

        this.elmTxt = this.elm.appendChild(document.createElement("div"));
        this.elmTxt.innerHTML = text;

        var el = this.elm.appendChild(document.createElement("div"));
        buttons.forEach(element => {
            var btn = el.appendChild(document.createElement("input"));
            btn.type = "button";
            btn.classList.add("formButton");
            btn.value = element;
            btn.onclick = ev => {
                this.close(element, onClose);
            }
        });
    }
    close(btn = [], fn = (button, std) => { }) {
        var std = { vl: true };
        fn(btn, std);
        if (std.vl) this.bkelm.remove();
    }
}

class serverCall {
    blk;
    constructor(module, command, fn = resp => { }, prm = {}, headers = {}) {
        prm.mdl = module;
        prm.cmd = command;

        var options = {
            method: "POST",
            body: JSON.stringify(prm),
            headers: headers
        };

        this.blk = new popupWin("загрузка...", []);
        try {
            fetch('index.php', options).then(resp => { this.onResp(resp) });
        } catch (err) {

        }
    }

    onResp(resp) {
        if (resp.ok) resp.json().then(jsResp => { this.onJSON(jsResp) });

    }

    onJSON(jsResp) {
        if (jsResp.err) {
            this.blk.close();

            if (jsResp.result == "auth failure") {
                if (document.forms.loginForm)
                    new popupWin("неверный логин или пароль");
                else
                    new authForm();
            } else {
                new popupWin(jsResp.result);
            };
        }
    }
}