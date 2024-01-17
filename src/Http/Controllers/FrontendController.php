<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactStoreRequest;
use App\Models\ContactMessage;
use App\Models\SubCategory;
use App\Services\BrandService;
use App\Services\FaqService;
use App\Services\SubscriptionService;
use App\Services\HowItWorkService;
use App\Services\TestimonialService;

class FrontendController extends Controller
{
    public $brandService, $subscriptionService, $howItWorkService, $testimonialService, $faqService;

    public function __construct()
    {
        $this->brandService = new BrandService;
        $this->subscriptionService = new SubscriptionService;
        $this->howItWorkService = new HowItWorkService;
        $this->testimonialService = new TestimonialService;
        $this->faqService = new FaqService;
    }

    public function index()
    {
        $data['brands'] = $this->brandService->getActiveAll();
        $data['plans'] = $this->subscriptionService->getAllForHome();
        $data['generateContentSubCategories'] = SubCategory::whereIn('id', json_decode(getOption('home_generate_content_section_category', '[]')))
            ->where('status', ACTIVE)->get();
        $data['howItWorks'] = $this->howItWorkService->getActiveAll();
        $data['testimonials'] = $this->testimonialService->getActiveAll();
        $data['faqs'] = $this->faqService->getActiveAll();
        return view('frontend.index', $data);
    }

    public function contactUs()
    {
        $data['faqs'] = $this->faqService->getActiveAll();
        return view('frontend.contact-us', $data);
    }


    public function contactUsStore(ContactStoreRequest $request)
    {
        $contactMessage = new ContactMessage();
        $contactMessage->name = $request->name;
        $contactMessage->email = $request->email;
        $contactMessage->phone = $request->phone;
        $contactMessage->subject = $request->subject;
        $contactMessage->message = $request->message;
        $contactMessage->save();
        return redirect()->back()->with('success', __(SENT_SUCCESSFULLY));
    }

     public function pages($slug)
    {
        if(!in_array($slug, ['privacy_policy', 'terms_and_condition', 'about_us','distance-sales-agreement'])){
            abort(404);
        }

        $data['page'] = getOption($slug, '');

        if ($slug == 'privacy_policy') {
            $data['title'] = __('Privacy Policy');
        } elseif ($slug == 'terms_and_condition') {
            $data['title'] = __('Terms And Conditions');
        } elseif ($slug == 'about_us') {
            $data['title'] = __('About Us');
        }elseif ($slug == 'distance-sales-agreement') {
            $data['title'] = __('Distance Sales Agreement');
        }

        return view('frontend.pages', $data);
    }
}
