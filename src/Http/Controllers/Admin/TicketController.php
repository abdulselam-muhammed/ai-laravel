<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketReplyRequest;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public $ticketService;

    public function __construct()
    {
        $this->ticketService = new TicketService;
    }

    public function index()
    {
        $data['pageTitle'] = 'Tickets';
        $data['tickets'] = $this->ticketService->getAll();
        return view('admin.tickets.index', $data);
    }

    public function details($id)
    {
        $data['pageTitle'] = 'Ticket Details';
        $data['navmmActiveClass'] = 'mm-active';
        $data['navActiveClass'] = 'active';
        $data['ticket']  = $this->ticketService->getById($id);
        $data['replies'] = $this->ticketService->getRepliesByTicketId($id);
        return view('admin.tickets.details', $data);
    }

    public function reply(TicketReplyRequest $request)
    {
        return $this->ticketService->reply($request);
    }

    public function statusChange(Request $request)
    {
        return $this->ticketService->statusChange($request);
    }
}
