<?php use App\Core\Csrf; ?>
<div class="page-header"><div><h1 class="page-title">New Support Ticket</h1></div></div>
<div class="card"><div class="card-body"><form method="POST" action="/tickets"><?= Csrf::field() ?>
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Subject</label><input type="text" class="form-control" name="subject" required></div>
<div class="col-md-2"><label class="form-label">Category</label><select class="form-select" name="category"><option value="general">General</option><option value="billing">Billing</option><option value="technical">Technical</option><option value="gateway">Gateway</option></select></div>
<div class="col-md-2"><label class="form-label">Priority</label><select class="form-select" name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></div>
<div class="col-12"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="6" required></textarea></div>
</div><button type="submit" class="btn btn-primary btn-lg mt-3"><i class="bi bi-ticket"></i> Submit Ticket</button>
</form></div></div>
