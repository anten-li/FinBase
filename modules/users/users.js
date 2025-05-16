"use strict";
class authForm extends popupWin {
    frm;
    onAuthEx;
    constructor(fn = usr => { }) {
        super("", ["ок"], (button, std) => {
            this.auth(std);
        });
        this.onAuthEx = fn;

        this.frm = document.createElement("form");
        this.frm.name = "loginForm";
        this.frm.classList.add("vrt-form");
        this.frm.append(
            baseApp.createInput("user", "Пользователь:"),
            baseApp.createInput("pass", "Пароль:", "password")
        )

        this.elmTxt.append(this.frm);
    }

    auth(std) {
        std.vl = false;

        var usr = this.frm.user.value;
        if (!usr) {
            new popupWin("не указан пользователь");
            return;
        }

        var pwd = this.frm.pass.value;
        if (!pwd) {
            new popupWin("не указан пароль");
            return;
        }

        new serverCall(
            "users",
            "getUserToken",
            resp => { this.onAuth(resp) },
            {},
            { Authorization: "Basic " + baseApp.b64EncodeUnicode(`${usr}:${pwd}`) }
        );
    }

    onAuth(resp) {
        this.close();
        this.onAuthEx(resp);
    }
}