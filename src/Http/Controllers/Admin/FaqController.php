<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaqRequest;
use App\Services\FaqService;

class FaqController extends Controller
{
    public $faqService;

    public function __construct()
    {
        $this->faqService = new FaqService;
    }

    public function index()
    {
        $data['pageTitle'] = __("Faq");
        $data['subFaqActiveClass'] = 'active';
        $data['faqs'] = $this->faqService->getAll();
        return view('admin.setting.faq', $data);
    }

    public function store(FaqRequest $request)
    {
        return $this->faqService->store($request);
    }

    public function destroy($id)
    {
        return $this->faqService->delete($id);
    }
}
