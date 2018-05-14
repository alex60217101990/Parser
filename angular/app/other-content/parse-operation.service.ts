import {Injectable, NgZone} from '@angular/core';
import {Observable} from 'rxjs/Observable';
import {HttpClient} from '@angular/common/http';
import {Parsing} from '../event.service';

@Injectable()
export class ParseOperationService {

  constructor(private http: HttpClient, private ngZone: NgZone) {}

    get parseStart(): Observable<boolean> {
        return this.http.post<Result>('/content/parseStart', {activate: 'start'})
            .map(result => {
                console.log(result);
              return result.result;
            });
    }

    public parserStartAsync($settings: Parsing): Observable<boolean>{
        return this.http.post<Result>('/content/parseStart', $settings)
            .map(result => {
                console.log(result);
                return result.result;
            });
    }

    public startProxyParse(): any{
        return this.http.post('/content/proxyParseStart', 1)
            .map(result => { return result; });
    }
}

export interface Result{
  result: boolean;
}


