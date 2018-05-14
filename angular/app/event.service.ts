import {EventEmitter, Injectable} from '@angular/core';
import {Ad} from './main-content/cities.service';

@Injectable()
export class EventService {
    public result$: EventEmitter<string>;
  constructor() {
      this.result$ = new EventEmitter<string>();
  }

  public getSignal(text: string){
      this.result$.emit(text/*'Server error: code 401.'*/);
  }
}



@Injectable()
export class EventLogoutService {
    public true$: EventEmitter<boolean>;
    constructor() {
        this.true$ = new EventEmitter<boolean>();
    }

    public getSignal(indicate: boolean){
        this.true$.emit(indicate);
    }
}



@Injectable()
export class EventRouteService {
    public path$: EventEmitter<string>;
    constructor() {
        this.path$ = new EventEmitter<string>();
    }

    public getSignal(url: string){
        this.path$.emit(url);
    }
}


@Injectable()
export class EventTitleService {
    public title$: EventEmitter<string>;
    constructor() {
        this.title$ = new EventEmitter<string>();
    }

    public getSignal(login: string){
        this.title$.emit(login);
    }
}



@Injectable()
export class EventFormService {
    public form$: EventEmitter<string>;
    constructor() {
        this.form$ = new EventEmitter<string>();
    }

    public getSignal(login: string){
        this.form$.emit(login);
    }
}

@Injectable()
export class EventAdService {
    public Ad$: EventEmitter<Ad>;
    constructor() {
        this.Ad$ = new EventEmitter<Ad>();
    }

    public getSignal(ad: Ad){
        this.Ad$.emit(ad);
    }
}


@Injectable()
export class EventAvatarLoad {
    public url$: EventEmitter<string>;
    constructor() {
        this.url$ = new EventEmitter<string>();
    }

    public getSignal(ad: string){
        this.url$.emit(ad);
    }
}




@Injectable()
export class EventAvatarReRender {
    public render_event$: EventEmitter<string>;
    constructor() {
        this.render_event$ = new EventEmitter<string>();
    }

    public getSignal($render: string){
        this.render_event$.emit($render);
    }
}


@Injectable()
export class EventStartProcess {
    public start_event$: EventEmitter<Parsing>;
    constructor() {
        this.start_event$ = new EventEmitter<Parsing>();
    }

    public getSignal($event: Parsing){
        this.start_event$.emit($event);
    }
}

export interface Parsing{
    $event: boolean;
    region: string;
    number_of_pages: number;
}