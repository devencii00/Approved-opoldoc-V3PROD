<?php

namespace App\Http\Controllers;

use App\Models\ChatbotOption;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected function formatOption(ChatbotOption $option): array
    {
        return [
            'id' => $option->id,
            'parent_id' => $option->parent_id,
            'button_text' => $option->button_text,
            'response_text' => $option->response_text,
            'is_starting_option' => (bool) $option->is_starting_option,
            'sort_order' => (int) ($option->sort_order ?? 0),
            'created_at' => $option->created_at,
            'updated_at' => $option->updated_at,
        ];
    }

    protected function buildTree($options): array
    {
        $nodes = [];
        foreach ($options as $opt) {
            $nodes[$opt->id] = array_merge($this->formatOption($opt), ['children' => []]);
        }

        $roots = [];
        foreach ($nodes as $id => $node) {
            $parentId = $node['parent_id'];
            if ($parentId && isset($nodes[$parentId])) {
                $nodes[$parentId]['children'][] = &$nodes[$id];
            } else {
                $roots[] = &$nodes[$id];
            }
        }

        $sort = function (&$list) use (&$sort) {
            usort($list, function ($a, $b) {
                $ao = (int) ($a['sort_order'] ?? 0);
                $bo = (int) ($b['sort_order'] ?? 0);
                if ($ao !== $bo) return $ao <=> $bo;
                return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
            });
            foreach ($list as &$item) {
                if (! empty($item['children'])) {
                    $sort($item['children']);
                }
            }
        };

        $sort($roots);

        return $roots;
    }

    public function config(Request $request)
    {
        $options = ChatbotOption::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'greeting' => 'How can I help you today?',
            'options' => $options->map(function (ChatbotOption $o) {
                return $this->formatOption($o);
            })->values(),
        ]);
    }

    public function options(Request $request)
    {
        $options = ChatbotOption::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $flat = $options->map(function (ChatbotOption $o) {
            return $this->formatOption($o);
        })->values();

        $tree = $this->buildTree($options);

        return response()->json([
            'flat' => $flat,
            'tree' => $tree,
        ]);
    }

    public function storeOption(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:chatbot_system,id'],
            'button_text' => ['required', 'string', 'max:255'],
            'response_text' => ['required', 'string', 'max:5000'],
            'is_starting_option' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $parentId = $validated['parent_id'] ?? null;
        $isStarting = (bool) ($validated['is_starting_option'] ?? false);
        if ($parentId) {
            $isStarting = false;
        }

        $option = ChatbotOption::create([
            'parent_id' => $parentId,
            'button_text' => $validated['button_text'],
            'response_text' => $validated['response_text'],
            'is_starting_option' => $isStarting,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return response()->json($this->formatOption($option), 201);
    }

    public function updateOption(Request $request, ChatbotOption $chatbotOption)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:chatbot_system,id'],
            'button_text' => ['required', 'string', 'max:255'],
            'response_text' => ['required', 'string', 'max:5000'],
            'is_starting_option' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $parentId = $validated['parent_id'] ?? null;
        if ($parentId && (int) $parentId === (int) $chatbotOption->id) {
            return response()->json(['message' => 'Parent option cannot be itself.'], 422);
        }

        $isStarting = (bool) ($validated['is_starting_option'] ?? false);
        if ($parentId) {
            $isStarting = false;
        }

        $chatbotOption->update([
            'parent_id' => $parentId,
            'button_text' => $validated['button_text'],
            'response_text' => $validated['response_text'],
            'is_starting_option' => $isStarting,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return response()->json($this->formatOption($chatbotOption));
    }

    public function destroyOption(ChatbotOption $chatbotOption)
    {
        $chatbotOption->delete();

        return response()->json(['ok' => true]);
    }
}
