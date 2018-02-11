# mdp run
suppressMessages(library(data.table))
suppressMessages(library(ggplot2))
suppressMessages(library(plotly))
source("/usr/lib/mdp.R")
args <- commandArgs(trailingOnly = TRUE)
exp_file <- args[1] #"GSE19439_data.tsv"
phen_file <- args[2] #"GSE19439_pdata.tsv"
control <- args[3] #"healthy"
method <- args[4] #"median"
std <- as.numeric(args[5]) #2
perc <- as.numeric(args[6]) #0.25
folder <- args[7] #"."
#args[8] ="cellspecific_modules_extended.gmt"
#args[9] = "Tcell"


# load expression data, gene symbols in the rows and sample names in columns
exp <- fread(paste(folder,exp_file,sep = "/"), data.table=F)
rownames(exp) <- exp$Symbol
exp <- exp[,2:ncol(exp)]
           
# load pheno data - has mandatory headers of Class and Sample
dataP <- fread(paste(folder,phen_file, sep = "/"), data.table=F)

# control = "healthy" # must specify the control class

if(is.na(args[8])) { 
  # run MDP
  
  mdp.results <- mdp(data = exp, pdata = dataP, control_lab = control, measure = method,
                     std = std, perc = perc,
                     print = F, save_tables = F)
  
  # pathways results that will be used for web interface
  pathways <- mdp.results$pathways # to generate sMDP plot, for each pathway
  path_out <- t(pathways[,c(1,5:ncol(pathways))])
  
  pathways_out <- cbind(path_out,rbind(colnames(dataP)[2:ncol(dataP)],
                                         rep("",ncol(dataP)-1)
                                         ,as.matrix(dataP[,2:ncol(dataP)])))
  rownames(pathways_out)[1] <- ""
  write.table(pathways_out , file = "MDP_scores.tsv", sep = "\t", quote = F, col.names = F)
  
  infovars <- colnames(dataP)[which(!colnames(dataP) %in% c("Sample", "Class"))]
  x = as.data.frame(t(pathways[1,6:ncol(pathways)]))
  colnames(x) ="value"
  x$Sample = rownames(x)
  x$Condition = dataP$Class
  x$Sample = factor(x$Sample,levels=rownames(x)[order(x$value)])
  if(ncol(dataP)==3){
    x$info <- paste0(infovars, ": ", dataP[,3],"|")
  }else if(ncol(dataP)>3) {
    x$info <- apply(dataP[,-which(colnames(dataP) %in% c("Sample", "Class"))], 1, 
                    function(...) paste(paste0(infovars, ": ", ...), collapse = "|"))
  } else {
    x$info = ""
  }
  
  p <- ggplotly(ggplot(x, aes(x = Sample, y = value, fill = Condition,
                     text = gsub("\\|", "\n", info))) + 
    geom_bar(stat = "identity") + 
    theme(axis.text.x = element_blank()) + ggtitle("MDP score using all genes") +
    labs(fill = "Coditions", x = 'Samples', y = 'MDP score'))
  htmlwidgets::saveWidget(as_widget(p), paste0(folder, "/plot1.html"))
  p2 <- ggplotly(ggplot(x, aes(x = Condition, y = value, color = Condition,
                     text = gsub("\\|", "\n", info))) + 
    geom_boxplot(alpha=0.4, outlier.shape = NA, outlier.size = 0) +
    geom_jitter(position=position_jitter(0.3)) +
    theme(axis.text.x = element_blank()) +
    labs(color = "Coditions", x = '', y = 'MDP scores'))
    htmlwidgets::saveWidget(as_widget(p2), paste0(folder, "/plot2.html"))
    
    x2 = as.data.frame(t(pathways[2,6:ncol(pathways)]))
    colnames(x2) ="value"
    x2$Sample = rownames(x2)
    x2$Condition = dataP$Class
    x2$Sample = factor(x2$Sample,levels=rownames(x2)[order(x2$value)])
    if(ncol(dataP)==3){
      x2$info <- paste0(infovars, ": ", dataP[,3],"|")
    }else if(ncol(dataP)>3) {
      x2$info <- apply(dataP[,-which(colnames(dataP) %in% c("Sample", "Class"))], 1, 
                      function(...) paste(paste0(infovars, ": ", ...), collapse = "|"))
    } else {
      x2$info = ""
    }
    p3 <- ggplotly(ggplot(x2, aes(x = Sample, y = value, fill = Condition,
                        text = gsub("\\|", "\n", info))) + 
      geom_bar(stat = "identity") + 
      theme(axis.text.x = element_blank()) + ggtitle("MDP scores using only perturbed genes") +
      labs(fill = "Coditions", x = 'Samples', y = 'MDP score'))
    htmlwidgets::saveWidget(as_widget(p3), paste0(folder, "/plot3.html"))
    p4 <- ggplotly(ggplot(x2, aes(x = Condition, y = value, color = Condition,
                       text = gsub("\\|", "\n", info))) + 
      geom_boxplot(alpha=0.4, outlier.shape = NA, outlier.size = 0) + 
      geom_jitter(position=position_jitter(0.3)) +
      theme(axis.text.x = element_blank()) +
      labs(color = "Coditions", x = '', y = 'MDP scores'))
    htmlwidgets::saveWidget(as_widget(p4), paste0(folder, "/plot4.html"))
} else {
  # load gmt file
  cell.pathways <- read_gmt(paste(folder,args[8]
                                  , sep = "/"))
  selected_path <- args[9]
  # run MDP with pathways
  mdp.results <- mdp(data = exp, pdata = dataP, control_lab = control, measure = method,
                     std = std, perc = perc,
                     print = F, save_tables = F, pathways = cell.pathways)
  pathways <- mdp.results$pathways 
  path_out <- t(pathways[pathways[,1]==selected_path,c(1,5:ncol(pathways))])
  
  pathways_out <- cbind(path_out,rbind(colnames(dataP)[2:ncol(dataP)],rep("",ncol(dataP)-1)
                                        ,as.matrix(dataP[,2:ncol(dataP)])))
  rownames(pathways_out)[1] <- ""
  write.table(pathways_out , file = "MDP_scores.tsv", sep = "\t", quote = F, col.names = F)
  x = as.data.frame(t(pathways[pathways[,1]==selected_path,6:ncol(pathways)]))
  infovars <- colnames(dataP)[which(!colnames(dataP) %in% c("Sample", "Class"))]
  colnames(x) ="value"
  x$Sample = rownames(x)
  x$Condition = dataP$Class
  x$Sample = factor(x$Sample,levels=rownames(x)[order(x$value)])
  if(ncol(dataP)==3){
    x$info <- paste0(infovars, ": ", dataP[,3],"|")
  }else if(ncol(dataP)>3) {
    x$info <- apply(dataP[,-which(colnames(dataP) %in% c("Sample", "Class"))], 1, 
                    function(...) paste(paste0(infovars, ": ", ...), collapse = "|"))
  } else {
    x$info = ""
  }
  p <- ggplotly(ggplot(x, aes(x = Sample, y = value, fill = Condition,
                     text = gsub("\\|", "\n", info))) + 
    geom_bar(stat = "identity") + 
    theme(axis.text.x = element_blank()) + 
    ggtitle(paste("MDP score using genes from ",selected_path, " pathway",sep="")) +
    labs(fill = "Coditions", x = 'Samples', y = 'MDP score'))
  htmlwidgets::saveWidget(as_widget(p), paste0(folder, "/plot1.html"))
  p2 <- ggplotly(ggplot(x, aes(x = Condition, y = value, color = Condition,
                     text = gsub("\\|", "\n", info))) + 
    geom_boxplot(alpha=0.4, outlier.shape = NA, outlier.size = 0) +
    geom_jitter(position=position_jitter(0.3)) +
    theme(axis.text.x = element_blank()) +
    labs(color = "Coditions", x = '', y = 'MDP scores'))
  htmlwidgets::saveWidget(as_widget(p2), paste0(folder, "/plot2.html"))
}
