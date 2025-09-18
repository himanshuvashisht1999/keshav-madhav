<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\GeneralSettings;
use Illuminate\Support\Facades\Hash;
class GeneralSettingsService {
    public function __construct(
        GeneralSettings $user
    ) {
        $this->user= $user;

    }

    public function getSingleData(Request $request){
        $data = GeneralSettings::first();
        // dd($data);
        return $data;
    }
    public function update(Request $request){
        // dd($request->all());
        $update_data = GeneralSettings::find($request->id);
        if($request->file('fav_icon')){
            $oldImageName = $update_data->getRawOriginal('fav_icon');
            if ($oldImageName) {
                $oldImagePath = public_path('assets/general-settings-image/' . $oldImageName);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $fav_icon = $request->file('fav_icon');
            $extIcon = $fav_icon->getClientOriginalExtension();
            $imgIcon = "fav_icon-".rand()."_".time().".".$extIcon;
            $destinationPath = public_path().'/assets/general-settings-image';
            $fav_icon->move($destinationPath, $imgIcon);
            $update_data->fav_icon = $imgIcon;
        }
        if($request->file('logo')){
            $oldImageName = $update_data->getRawOriginal('logo');
            if ($oldImageName) {
                $oldImagePath = public_path('assets/general-settings-image/' . $oldImageName);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $logo = $request->file('logo');
            $extIcon = $logo->getClientOriginalExtension();
            $imgIcon = "logo-".rand()."_".time().".".$extIcon;
            $destinationPath = public_path().'/assets/general-settings-image';
            $logo->move($destinationPath, $imgIcon);
            $update_data->logo = $imgIcon;
        }
        $update_data->phone = $request->phone;
        $update_data->email = $request->email;
        $update_data->address = $request->address;
        $update_data->sub_heading = $request->sub_heading;
        $update_data->copy_right = $request->copy_right;
        $update_data->facebook = $request->facebook;
        $update_data->twitter = $request->twitter;
        $update_data->instagram = $request->instagram;
        $update_data->linkedin = $request->linkedin;
        $update_data->android_app = $request->android_app;
        $update_data->ios_app = $request->ios_app;
        $update_data->title = $request->title;
        $update_data->meta_title = $request->meta_title;
        $update_data->website_name = $request->website_name;
        $update_data->meta_keywords = $request->meta_keywords;
        $update_data->meta_description = $request->meta_description;
        $update_data->header_script = $request->header_script;
        $update_data->footer_script = $request->footer_script;
        $update_data->save();

        return true;
    }


}