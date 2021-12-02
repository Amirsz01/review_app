'use strict';

import { Controller } from 'stimulus';

export default class extends Controller {
    connect() {
        this.element.addEventListener('dropzone-multiple:connect', this._onConnect);
        this.element.addEventListener('dropzone-multiple:change', this._onChange);
        this.element.addEventListener('dropzone-multiple:clear', this._onClear);
    }

    disconnect() {
        // You should always remove listeners when the controller is disconnected to avoid side-effects
        this.element.removeEventListener('dropzone-multiple:connect', this._onConnect);
        this.element.removeEventListener('dropzone-multiple:change', this._onChange);
        this.element.removeEventListener('dropzone-multiple:clear', this._onClear);
    }

    _onConnect(event) {
        // The dropzone was just created
    }

    _onChange(event) {
        console.log(event)
    }

    _onClear(event) {
        // The dropzone has just been cleared
    }
}
