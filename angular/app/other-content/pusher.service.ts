declare var Pusher: any;
import { Injectable, Output, EventEmitter } from '@angular/core';
import {Observable} from 'rxjs/Observable'
import {BehaviorSubject} from "rxjs/Rx";

@Injectable()
export class PusherService {
    private pusher: any;

    constructor() {
        console.log('PusherService', 'constructor');
        this.pusher = new Pusher('XXXXXXXXXXXXXXXXXXXX', {
            cluster: 'eu',
            encrypted: true
        });
        this.pusher.logToConsole = true;

        let channel = this.pusher.subscribe('my-channel');
        channel.bind('my-event',  (data) => {
            console.log(data.message);
        });
    }
}