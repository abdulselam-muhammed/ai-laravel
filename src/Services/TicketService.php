<?php

namespace App\Services;

use App\Models\FileManager;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class TicketService
{
    use ResponseTrait;
    public function getAll()
    {
        return Ticket::query()
            ->with('topic')
            ->get();
    }

    public function getAllByPropertyId($id)
    {
        return Ticket::query()
            ->get();
    }

    public function getAllByUnitId($id)
    {
        return Ticket::query()
            ->get();
    }

    public function searchByUnitId($request, $unit_id)
    {
        return Ticket::query()
            ->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('details', 'like', "%{$request->search}%");
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->get();
    }

    public function getById($id)
    {
        return Ticket::query()->findOrFail($id);;
    }

    public function getRepliesByTicketId($id)
    {
        return TicketReply::query()
            ->join('users', 'ticket_replies.user_id', '=', 'users.id')
            ->select('ticket_replies.*', 'users.first_name', 'users.last_name', 'users.role')
            ->where('ticket_replies.ticket_id', $id)
            ->get();
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($id != '') {
                $ticket = Ticket::findOrFail($request->id);
            } else {
                $ticket =  new Ticket();
            }
            $user = auth()->user();

            $ticket->user_id = $user->id;
            $ticket->title = $request->title;
            $ticket->details = $request->details;
            $ticket->topic_id = $request->topic_id;
            $ticket->save();

            if ($request->hasFile('attachments')) {
                foreach ($request->attachments as $key => $attachment) {
                    $newFile = new FileManager();
                    $upload = $newFile->upload('Ticket', $attachment);
                    if ($upload['status']) {
                        $upload['file']->origin_id = $ticket->id;
                        $upload['file']->origin_type = "App\Models\Ticket";
                        $upload['file']->save();
                    } else {
                        throw new Exception($upload['message']);
                    }
                }
            }
            DB::commit();
            $message = __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function reply($request)
    {
        DB::beginTransaction();
        try {
            $ticket = Ticket::findOrFail($request->ticket_id);

            if (in_array($ticket->status, [TICKET_STATUS_CLOSE, TICKET_STATUS_RESOLVED])) {
                $ticket->status = TICKET_STATUS_REOPEN;
                $ticket->save();
            }

            $reply =  TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'reply' => $request->reply
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->attachments as $key => $attachment) {
                    $newFile = new FileManager();
                    $upload = $newFile->upload('TicketReply', $attachment);
                    if ($upload['status']) {
                        $upload['file']->origin_id = $reply->id;
                        $upload['file']->origin_type = "App\Models\TicketReply";
                        $upload['file']->save();
                    } else {
                        throw new Exception($upload['message']);
                    }
                }
            }
            DB::commit();
            $message = __(SENT_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function statusChange($request)
    {
        DB::beginTransaction();
        try {
            $ticket = Ticket::findOrFail($request->id);
            $ticket->status = $request->status;
            $ticket->save();
            DB::commit();
            $message = __(STATUS_UPDATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function delete($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();
        return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
    }
}
