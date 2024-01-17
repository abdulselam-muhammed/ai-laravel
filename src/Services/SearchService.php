<?php

namespace App\Services;

use App\Models\Category;
use App\Models\SearchResult;
use App\Models\SearchResultItem;
use App\Models\SubCategory;
use App\Traits\ResponseTrait;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use OpenAI;

class SearchService
{
    use ResponseTrait;

    public function searchStore($request)
    {
        DB::beginTransaction();
        try {
            $subCategory = $this->getSubCategoryById($request->sub_category_id);


            $description = $request->description;
            $product = $request->product;
            $keyword = $request->keyword;
            $creativity = $request->creativity_level;
            $output = $request->output;
            $tone_of_voice = $request->tone_of_voice;
            $target = $request->target_action;
            $language = $request->language;

            $prompt = $subCategory->prompt;
            // $prompt = 'Write me a headline for #product#, my description is #description#';

            foreach (json_decode($subCategory->content) as $content) {
                $name = $content->name;
                $prompt = str_replace("#" . $name . "#", $request->$name, $prompt);
            }
            $maxToken = (int)getLimit(RULES_CHARACTER)  < $request->character ? (int)getLimit(RULES_CHARACTER) / 4  : $request->character / 4;

            //long_form character
            if ($request->long_form == 1) {
                $prompt = $prompt . ' in long-form using ' . $language . ' language'. 'temperature' .(float)$creativity .'max token' .(int)$maxToken ?? 1 .'output' . (int)$output ;
                // $maxToken = 1500;
            } else {
                $prompt = $prompt . ' in ' . $language . ' language.'. 'temperature' .(float)$creativity .'max token' .(int)$maxToken ?? 1 .'output' . (int)$output  ;
            }
            

            // $maxToken = (int)getLimit(RULES_CHARACTER) / 4 < 1000 ? (int)getLimit(RULES_CHARACTER) / 4 : 1000;
            $client = OpenAI::client('you-sk-key');
            // $result = $client->completions()->create([
            //     "model" => 'text-davinci-003',
            //     "temperature" => (float)$creativity,
            //     'max_tokens' => (int)$maxToken ?? 1,
            //     'prompt' => sprintf($prompt),
            //     'n' => (int)$output,
            // ]);
            
            //new oprn ai optimaztion
            $result = $client->completions()->create([
                "model" => 'gpt-3.5-turbo-instruct',
                'prompt' => sprintf($prompt)
            ]);

            $getApiResponseText = $result['choices']; // if n=2 then choices become 2
            $searchResult = new SearchResult();
            $searchResult->user_id = auth()->id();
            $searchResult->category_id = $subCategory->category_id;
            $searchResult->sub_category_id = $subCategory->id;
            $searchResult->description = $description;
            $searchResult->product = $product;
            $searchResult->prompt = $prompt;
            $searchResult->creativity_level = $creativity;
            $searchResult->tone_of_voice = $tone_of_voice;
            $searchResult->target_action = $target;

            $searchResult->save();

            if ($searchResult->save()) {
                foreach ($getApiResponseText as $choice) {
                    $resultItem = new SearchResultItem();
                    $resultItem->search_result_id = $searchResult->id;
                    $resultItem->output = $choice['text'];
                    $resultItem->total_word = str_word_count($choice['text']);
                    $resultItem->total_characters = strlen($choice['text']);
                    $resultItem->user_id = auth()->id();
                    $resultItem->save();
                }
            }

            $data['searchResult'] = $searchResult;
            $items = $searchResult->items;
            $data['view'] = view('user.search.output', compact('items'))->render();

            DB::commit();
            $message = __(CREATED_SUCCESSFULLY);
            return $this->success($data, $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function searchItemUpdate($request)
    {
        DB::beginTransaction();
        try {
            $resultItem = SearchResultItem::query()
                ->when(auth()->user()->role == USER_ROLE_USER, function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->findOrFail($request->id);
            $resultItem->output = $request->output;
            $resultItem->save();

            DB::commit();
            $message = __(UPDATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function searchItemFavorite($request)
    {
        DB::beginTransaction();
        try {
            $resultItem = SearchResultItem::query()
                ->when(auth()->user()->role == USER_ROLE_USER, function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->findOrFail($request->id);
            $resultItem->is_favorite = $resultItem->is_favorite == ACTIVE ? DEACTIVATE : ACTIVE;
            $resultItem->save();

            DB::commit();
            $message = __(UPDATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function searchItemReact($request)
    {
        DB::beginTransaction();
        try {
            $resultItem = SearchResultItem::query()
                ->when(auth()->user()->role == USER_ROLE_USER, function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->findOrFail($request->id);
            $resultItem->react = intval($request->react);
            $resultItem->save();

            DB::commit();
            $message = __(UPDATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function searchItemDelete($request)
    {
        DB::beginTransaction();
        try {
            $resultItem = SearchResultItem::query()
                ->when(auth()->user()->role == USER_ROLE_USER, function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->findOrFail($request->id);
            $resultItem->delete();
            DB::commit();
            $message = __(DELETED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function getSubCategoryById($id)
    {
        return SubCategory::findOrFail($id);
    }

    public function getActiveCategory()
    {
        return Category::where('status', ACTIVE)->get();
    }

    public function getTrashItem()
    {
        return SearchResultItem::query()
            ->onlyTrashed()
            ->when(auth()->user()->role == USER_ROLE_USER, function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('id', 'DESC')
            ->paginate(getOption('paginate_items', 10));
    }

    public function getReactItem($react)
    {
        return SearchResultItem::query()
            ->where('react', $react)
            ->when(auth()->user()->role == USER_ROLE_USER, function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('id', 'DESC')
            ->paginate(getOption('paginate_items', 10));
    }

    public function getAll()
    {
        return SearchResultItem::query()
            ->when(auth()->user()->role == USER_ROLE_USER, function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('id', 'DESC')
            ->paginate(getOption('paginate_items', 10));
    }

    public function downloadContent($request)
    {
        try {
            $data['item'] = SearchResultItem::findOrFail($request->id);
            $data['type'] = $request->type;
            if ($request->type == 'print') {
                return view('user.content.download', $data);
            } elseif ($request->type == 'word') {
                $headers = array(
                    "Content-type" => "text/html",
                    "Content-Disposition" => "attachment;Filename=" . time() . ".doc"
                );
                $view = view('user.content.download', $data);
                return \response()->make($view, 200, $headers);
            }
            return redirect()->back()->with('error', __(SOMETHING_WENT_WRONG));
        } catch (Exception $e) {
            return redirect()->back()->with('error', __(SOMETHING_WENT_WRONG));
        }
    }
}
