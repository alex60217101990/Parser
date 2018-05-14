import {Component, Inject, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {EventAdService, EventAvatarReRender, EventRouteService} from '../event.service';
import {Ad, CitiesService} from '../main-content/cities.service';

import { NgxGalleryOptions, NgxGalleryImage, NgxGalleryAnimation } from 'ngx-gallery';
import {MAT_DIALOG_DATA, MatDialog, MatDialogRef, MatSnackBar} from '@angular/material';
import {FormBuilder, FormControl, FormGroup, Validators} from '@angular/forms';

@Component({
  selector: 'app-ad',
  templateUrl: './ad.component.html',
  styleUrls: ['./ad.component.scss']
})
export class AdComponent implements OnInit, OnDestroy {

  public id: number;
  public ad: Ad = {id: 0, link: '', updated_at: new Date(), ad_from: '', type_of_structure: '',type_of_apartment: '', number_of_rooms: 0,
      floor: 1, storey_house: 1, total_area: 1, living_space: 0.0, kitchen_area: 0.0, price: 0.0, telephones: '',
      number_of_photos: 0, status: '', number_of_similar: 0, address: '', cause_of_change: '', exchange: false, formula_of_exchange: '',
      state: '', source_of_information: '', bathroom_type: '', wall_material: '', phone_line: false, having_a_bath: false, number_of_balconies: 0,
      notes: '', display_info: ''};

  constructor(private activateRoute: ActivatedRoute, private $ad: EventAdService,
              private connect_service: CitiesService,
              public dialog: MatDialog, private $renderAva: EventAvatarReRender, fb1: FormBuilder) {

        this.Size1 = fb1.group({
            width: new FormControl('', [
                Validators.required,
                Validators.pattern("^[1-9][0-9]{1,2}$"),
                Validators.max(700),
                Validators.min(50)
            ]),
            height: new FormControl('', [
                Validators.required,
                Validators.pattern("^[1-9][0-9]{1,2}$"),
                Validators.max(500),
                Validators.min(50)
            ]),
        });

      this.Size2 = fb1.group({
          width_one: new FormControl('', [
              Validators.required,
              Validators.pattern("^[1-9][0-9]{2,3}$"),
              Validators.max(1980),
              Validators.min(100)
          ]),
          height_one: new FormControl('', [
              Validators.required,
              Validators.pattern("^[1-9][0-9]{2,3}$"),
              Validators.max(1080),
              Validators.min(100)
          ]),
      });

        this.WidthAva = this.Size1.get('width');
        this.HeightAva = this.Size1.get('height');

        this.WidthImg = this.Size2.get('width_one');
        this.HeightImg = this.Size2.get('height_one');
  }

    /**
     * Open MatDialog method.
     * @param {string} Title
     * @param {string} Body
     */
    private openDialog(Title:string, Body:string): void {
        let dialogRef = this.dialog.open(DialogAvatar, {
            width: '40%',
            data: { name: Title, body: Body }
        });
        dialogRef.afterClosed().subscribe(result => {});
    }


   public galleryOptions: NgxGalleryOptions[];
   public galleryImages: NgxGalleryImage[];
   private Galary: Array<any> = new Array<any>();
   public ImagesList: Array<Image> = new Array<Image>();
   public toogleImg: boolean = false;


  ngOnInit():void {
      this.id = this.activateRoute.snapshot.params['id'];

      for(let item of this.cards2)
          item = false;


      this.LoadAllAdImages(this.activateRoute.snapshot.params['id']);

      this.galleryOptions = [
          {
              layout: "thumbnails-top",
              previewFullscreen: true,
              previewCloseOnClick: true,
              previewCloseOnEsc: true,
              previewZoom: true,
              previewRotate: true,


              width: '600px',
              height: '400px',
              thumbnailsColumns: 3,
              imageAnimation: NgxGalleryAnimation.Rotate/*Slide*/
          },
          {
              breakpoint: 800,
              width: '100%',
              height: '600px',
              imagePercent: 80,
              thumbnailsColumns: 2,

              thumbnailsPercent: 20,
              thumbnailsMargin: 20,
              thumbnailMargin: 20
          },
          {
              breakpoint: 400,
              preview: false
          }
      ];

      this.galleryImages = [
          {
              small: 'storage/images/00c3dbd74dcf66524338e926c9776c72.jpg',
              medium: 'storage/images/00c3dbd74dcf66524338e926c9776c72.jpg',
              big: 'storage/images/00c3dbd74dcf66524338e926c9776c72.jpg'
          }
      ];
  }

    private LoadAllAdImages(id: number){
        if(!!this.galleryImages) this.galleryImages.length = 0;
        if(!!this.ImagesList) this.ImagesList.length = 0;
        if(!!this.active) this.active.length = 0;
        if(!!this.Galary) this.Galary.length = 0;
        this.connect_service.getApPhotos(id).subscribe(data => {
    
            if(!!data) {
                for (let el of data['images']) {
                    this.ImagesList.push({id: el['id'], img_name: el['img_name'], img_path: el['img_path'], ad_id: el['ad_id']});
                    this.active.push(false);
                    this.Galary.push({small: el['img_path'], medium: el['img_path'], big: el['img_path']});
                }
                this.galleryImages = this.Galary;
                (this.Galary.length > 0) ? this.toogleImg = true : this.toogleImg = false;
            }
        });
    }

  ngOnDestroy(): void{
      this.galleryOptions.length = 0;
      this.galleryImages.length = 0;
      this.Galary.length = 0;
      this.connect_service.ngOnDestroy();
      this.ad = null;
      this.galleryOptions.length = 0;
      this.galleryImages.length = 0;
      this.active.length = 0;
      this.cards2.length = 0;
  }

  private files: any;
  public filename: string = 'selected file...';
  public select: boolean = true;
  public addPhoto($event):void {
      let target = $event.target || $event.srcElement;
      this.files = target.files;
      this.filename = target.files[0].name;
      this.select = false;
  }

  public uploadFile(): void{
      if (this.files) {
          let files :FileList = this.files;
          const formData = new FormData();
          for(let i = 0; i < files.length; i++){
              formData.append('avatar', files[i]);
              let arr = files[i].name.trim().split('.');
              formData.append('type', arr[arr.length-1]);
          }
          let AvaName = AdComponent.guid();
          formData.append('userName', AvaName);
          (!!this.WidthAva && parseInt(this.WidthAva.value.toString())>50 && parseInt(this.WidthAva.value.toString(),10)<=700)?
            formData.append('width', this.WidthAva.value.toString()):formData.append('width', ''+80);
          (!!this.HeightAva && parseInt(this.HeightAva.value.toString())>50 && parseInt(this.HeightAva.value.toString())<=500)?
              formData.append('height', this.HeightAva.value.toString()):formData.append('height', ''+60);
          this.connect_service.uploadPhoto(formData).subscribe(message =>{
              console.log(message);
                if(message.hasOwnProperty('success')) {
                    this.openDialog(AvaName+'.png ', message['success']);
                    this.$renderAva.getSignal('storage/avatars/'+message['name']);
                }
                if(message.hasOwnProperty('message'))
                    this.openDialog(AvaName+'.png ', message['message']);
          });
      }
  }

  public refreshFile():void{
    this.files = null;
    this.filename = 'selected file...';
    this.select = true;
  }
    public refreshFile1():void{
        this.files1 = null;
        this.filename1 = 'selected file...';
        this.select1 = true;
    }

    /**
     * Static method for generate GUID identifier.
     * @return {string}
     */
  static guid() {
      return AdComponent.s4() + AdComponent.s4() + '-' + AdComponent.s4() + '-' + AdComponent.s4() + '-' +
          AdComponent.s4() + '-' + AdComponent.s4() + AdComponent.s4() + AdComponent.s4();
  }
  static s4() {
      return Math.floor((1 + Math.random()) * 0x10000)
          .toString(16)
          .substring(1);
  }

    disconnect(): void {};

  public active:Array<boolean>=new Array<boolean>();

    /**
     * Delete ad image by id.
     * @param {number} id
     */
  public deleteImage(id: number):void{
     this.connect_service.deleteAdImage(id)
         .subscribe(response => {
             //console.log(response);
             if(response.hasOwnProperty('success')) {
                 this.openDialog(id+' image delete: ', response['success']);
                 this.LoadAllAdImages(this.activateRoute.snapshot.params['id']);
             }
             if(response.hasOwnProperty('message'))
                 this.openDialog(id+' image delete: ', response['message']);
         });
  }

  public toogleStyleActive(index: number):void{
      this.active[index]=true;
  }
    public toogleStylePassive(index: number):void{
        this.active[index]=false;
    }

    private files1: any;
    public filename1: string = 'selected file...';
    public select1: boolean = true;
    public addImages($event):void {
        let target = $event.target || $event.srcElement;
        this.files1 = target.files;
        this.filename1 = target.files[target.files.length-1].name;
        this.select1 = false;
        console.log(this.files1);
    }

    public uploadFiles():void{
        if (this.files1) {
            let files :FileList = this.files1;
            const formData = new FormData();
            let Names:Array<string> = new Array<string>();
            for(let i = 0; i < files.length; i++){
                formData.append('AdImages'+i, files[i]);
                formData.append('size', ''+(i+1));
                Names.push(files[i].name);
            }
            formData.append('ad_id', ''+this.id);
            (!!this.WidthImg && parseInt(this.WidthImg.value.toString())>=100 && parseInt(this.WidthImg.value.toString(),10)<=1920)?
                formData.append('width', this.WidthImg.value.toString()):formData.append('width', ''+320);
            (!!this.HeightImg && parseInt(this.HeightImg.value.toString())>=100 && parseInt(this.HeightImg.value.toString())<=1080)?
                formData.append('height', this.HeightImg.value.toString()):formData.append('height', ''+240);

            this.connect_service.uploadAdImages(formData).subscribe(message =>{
                console.log(message);
                if(message.hasOwnProperty('success')) {
                    this.openDialog(Names.join('; '), message['success']);
                    this.LoadAllAdImages(this.activateRoute.snapshot.params['id']);
                }
                if(message.hasOwnProperty('message'))
                    this.openDialog(Names.join('; '), message['message']);
            });
        }
    }

    /***********************************************************/
    protected cards2: Array<boolean>=new Array<boolean>(2);
    public variant1: number = 1;
    public variants1 = [1,2];
    protected variant2: number = 1;
    protected variants2 = [1,2];

    public Size1: FormGroup;
    protected WidthAva: any;
    protected HeightAva: any;

    protected Size2: FormGroup;
    protected WidthImg: any;
    protected HeightImg: any;

    /**
     * URL pattern.
     * /(https?:\/\/.*\.(?:png|jpg))/i
     */
    protected UrlFormControl: FormControl = new FormControl('', [
        Validators.required,
        Validators.pattern("(?:([^:/?#]+):)?(?://([^/?#]*))?([^?#]*\\.(?:jpg|gif|png))(?:\\?([^#]*))?(?:#(.*))?"),
    ]);

    protected UrlAdFormControl: FormControl = new FormControl('',[
        Validators.required,
        Validators.pattern("(?:([^:/?#]+):)?(?://([^/?#]*))?([^?#]*\\.(?:jpg|gif|png))(?:\\?([^#]*))?(?:#(.*))?"),
    ]);

    /**
     * Save URL avatar image.
     */
    protected saveAvatarUrl():void {
        if(!!this.UrlFormControl.value)
            this.connect_service.saveAvatarUrl(this.UrlFormControl.value.toString()).subscribe(response =>{
                if(response.hasOwnProperty('success')){
                    this.openDialog(this.UrlFormControl.value.toString()+' ', response['success']);
                    this.$renderAva.getSignal(this.UrlFormControl.value.toString());
                }
            });
    }

    /**
     * Save URL ad image.
     */
    protected saveAdImageUrl(): void {
        if(!!this.UrlAdFormControl.value)
            this.connect_service.saveAdUrlImage(this.UrlAdFormControl.value.toString(),this.id).subscribe(response =>{
                console.log(response);
                if(response.hasOwnProperty('success')){
                    this.openDialog(this.UrlAdFormControl.value.toString()+' ', response['success']);
                    this.LoadAllAdImages(this.activateRoute.snapshot.params['id']);
                }else{
                    this.openDialog(this.UrlAdFormControl.value.toString()+' ', response['message']);
                }
            });
    }



}

export interface ImageInterface {
    thumbnail?: any; //image src for the thumbnail
    image?: any; //image src for the image
    text?: string; //optional text to show for the image
    photo_id: number;
}

export interface Image {
    id: number;
    img_name: string;
    img_path: string;
    ad_id: number;
}



@Component({
    selector: 'dialog-avatar',
    templateUrl: 'dialog-avatar.html',
})
export class DialogAvatar {

    constructor(
        public dialogRef: MatDialogRef<DialogAvatar>,
        @Inject(MAT_DIALOG_DATA) public data: any) { }

    onNoClick(): void {
        this.dialogRef.close();
    }

}
