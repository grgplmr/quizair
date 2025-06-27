(function($){
    function shuffle(array){
        for(var i=array.length-1;i>0;i--){
            var j=Math.floor(Math.random()*(i+1));
            var t=array[i];array[i]=array[j];array[j]=t;
        }
    }
    function Quiz($el){
        this.$el=$el;
        this.category=$el.data('category');
        this.questions=[];
        this.queue=[];
        this.score=0;
        this.current=null;
        this.awaitingNext=false;
        this.init();
    }
    Quiz.prototype.init=function(){
        var self=this;
        $.getJSON(ACME_BIAQuiz.api,{category:self.category}).done(function(data){
            self.questions=data;
            shuffle(self.questions);
            self.queue=self.questions.slice();
            self.next();
        });
    };
    Quiz.prototype.next=function(){
        var self=this;
        if(!self.queue.length){
            self.$el.html('<p>Score: '+self.score+'/'+self.questions.length+'</p><button class="acme-biaquiz-restart">Relancer le quiz</button>');
            return;
        }
        self.awaitingNext=false;
        self.current=self.queue.shift();
        var html='<div class="bia-question"><p>'+self.current.title+'</p><ul>';
        $.each(self.current.choices,function(i,choice){
            html+='<li><button class="bia-choice" data-index="'+i+'">'+choice+'</button></li>';
        });
        html+='</ul></div>';
        self.$el.html(html);
    };
    Quiz.prototype.answer=function(index){
        if(this.awaitingNext){return;}
        if(index==this.current.answer){
            this.score++;
        }else{
            this.queue.push(this.current);
        }
        this.awaitingNext=true;
        var html='<div class="bia-explanation">';
        if(this.current.explanation){
            html+='<p>'+this.current.explanation+'</p>';
        }
        html+='<button class="bia-next">Suivant</button></div>';
        this.$el.find('.bia-question').append(html);
    };
    $(document).on('click','.bia-choice',function(e){
        e.preventDefault();
        var $btn=$(this);var index=$btn.data('index');
        var $quiz=$btn.closest('.acme-biaquiz').data('quiz');
        if($quiz){$quiz.answer(index);} });
    $(document).on('click','.bia-next',function(e){
        e.preventDefault();
        var $quiz=$(this).closest('.acme-biaquiz').data('quiz');
        if($quiz){$quiz.next();}
    });
    $(document).on('click','.acme-biaquiz-restart',function(){location.reload();});
    $(function(){
        $('.acme-biaquiz').each(function(){var q=new Quiz($(this));$(this).data('quiz',q);});
    });
})(jQuery);
