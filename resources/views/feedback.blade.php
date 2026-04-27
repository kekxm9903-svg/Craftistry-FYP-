@extends('layouts.app')

@section('title', 'Feedback')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/feedback.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">Feedback</span>
    </div>
</div>

<div class="fb-page">

    {{-- ══ PAGE HEADER CARD ══ --}}
    <div class="fb-page-header-card">
        <div class="fb-page-title">Share Your <span>Feedback</span></div>
        <div class="fb-page-subtitle">Help us improve Craftistry. Every message is read by our team.</div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="fb-alert fb-alert-success" id="fb-alert">
            <div class="fb-alert-icon"><i class="fas fa-check-circle"></i></div>
            <div class="fb-alert-body">
                <p class="fb-alert-title">Thank you for your feedback!</p>
                <p class="fb-alert-msg">{{ session('success') }}</p>
            </div>
            <button class="fb-alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="fb-alert fb-alert-error" id="fb-alert">
            <div class="fb-alert-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="fb-alert-body">
                <p class="fb-alert-title">Something went wrong</p>
                <p class="fb-alert-msg">{{ session('error') }}</p>
            </div>
            <button class="fb-alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- ══ TWO COLUMN LAYOUT ══ --}}
    <div class="fb-layout">

        {{-- ── FORM CARD ── --}}
        <div class="sp-card">
            <div class="sp-card-header">
                <div class="hline"></div>
                Write to Us
            </div>

            <form method="POST" action="{{ route('feedback.store') }}" id="feedbackForm" novalidate>
                @csrf

                <div class="sp-card-body">

                    {{-- Category --}}
                    <div class="fb-field {{ $errors->has('category') ? 'has-error' : '' }}">
                        <label class="fb-label">Category <span class="fb-required">*</span></label>
                        <div class="category-grid">

                            <label class="cat-option">
                                <input type="radio" name="category" value="general"
                                    {{ old('category', 'general') === 'general' ? 'checked' : '' }}>
                                <div class="cat-card">
                                    <div class="cat-icon general"><i class="fas fa-comments"></i></div>
                                    <span class="cat-label">General</span>
                                    <span class="cat-desc">Overall experience</span>
                                </div>
                            </label>

                            <label class="cat-option">
                                <input type="radio" name="category" value="bug"
                                    {{ old('category') === 'bug' ? 'checked' : '' }}>
                                <div class="cat-card">
                                    <div class="cat-icon bug"><i class="fas fa-bug"></i></div>
                                    <span class="cat-label">Bug Report</span>
                                    <span class="cat-desc">Something is broken</span>
                                </div>
                            </label>

                            <label class="cat-option">
                                <input type="radio" name="category" value="suggestion"
                                    {{ old('category') === 'suggestion' ? 'checked' : '' }}>
                                <div class="cat-card">
                                    <div class="cat-icon suggestion"><i class="fas fa-lightbulb"></i></div>
                                    <span class="cat-label">Suggestion</span>
                                    <span class="cat-desc">Ideas to improve</span>
                                </div>
                            </label>

                        </div>
                        @error('category')
                            <span class="fb-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Subject --}}
                    <div class="fb-field {{ $errors->has('subject') ? 'has-error' : '' }}">
                        <label class="fb-label" for="subject">
                            Subject <span class="fb-required">*</span>
                        </label>
                        <input type="text"
                               name="subject"
                               id="subject"
                               class="fb-input"
                               placeholder="Brief summary of your feedback"
                               value="{{ old('subject') }}"
                               maxlength="255"
                               required
                               autocomplete="off">
                        @error('subject')
                            <span class="fb-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div class="fb-field {{ $errors->has('message') ? 'has-error' : '' }}">
                        <label class="fb-label" for="message">
                            Message <span class="fb-required">*</span>
                        </label>
                        <textarea name="message"
                                  id="message"
                                  class="fb-textarea"
                                  placeholder="Describe your feedback in detail…"
                                  rows="6"
                                  maxlength="2000"
                                  required>{{ old('message') }}</textarea>
                        <div class="fb-char-row">
                            <span id="charCount">0</span> / 2000 characters
                        </div>
                        @error('message')
                            <span class="fb-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                </div>

                {{-- Form actions footer ── outside sp-card-body, inside form ── --}}
                <div class="form-actions">
                    <button type="submit" class="fb-submit" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Send Feedback
                    </button>
                </div>

            </form>
        </div>

        {{-- ── SIDEBAR ── --}}
        <aside class="fb-sidebar">

            <div class="fb-tip-card">
                <div class="fb-tip-icon shield">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h3>Confidential</h3>
                    <p>Your feedback is only visible to our admin team — never to other users.</p>
                </div>
            </div>

            <div class="fb-tip-card">
                <div class="fb-tip-icon bulb">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                    <h3>Have an idea?</h3>
                    <p>We love suggestions for new features or improvements to the platform.</p>
                </div>
            </div>

            <div class="fb-tip-card">
                <div class="fb-tip-icon bug">
                    <i class="fas fa-bug"></i>
                </div>
                <div>
                    <h3>Found a bug?</h3>
                    <p>Describe what happened and the steps to reproduce it so we can fix it quickly.</p>
                </div>
            </div>

            <div class="fb-categories-ref">
                <h4>Category Guide</h4>
                <div class="fb-cat-ref-item">
                    <span class="cat-dot general"></span>
                    <div>
                        <strong>General</strong>
                        <p>Compliments, overall experience, or anything else</p>
                    </div>
                </div>
                <div class="fb-cat-ref-item">
                    <span class="cat-dot bug"></span>
                    <div>
                        <strong>Bug Report</strong>
                        <p>Something isn't working as expected</p>
                    </div>
                </div>
                <div class="fb-cat-ref-item">
                    <span class="cat-dot suggestion"></span>
                    <div>
                        <strong>Suggestion</strong>
                        <p>Ideas to make Craftistry better</p>
                    </div>
                </div>
            </div>

        </aside>

    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/feedback.js') }}"></script>
@endsection